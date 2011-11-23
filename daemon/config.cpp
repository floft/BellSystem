#include "config.h"

Config::Config(string filename)
	:defaults(7)
{
	DomParser parser;
	parser.set_substitute_entities();
	parser.parse_file(filename);

	if (!parser) throw Error("invalid XML");

	const Node* root = parser.get_document()->get_root_node();

	NodeSet n_settings  = root->find("settings/*");
	NodeSet n_defaults  = root->find("calendar/default/exec");
	NodeSet n_quiets    = root->find("calendar/quiet/when");
	NodeSet n_overrides = root->find("calendar/override/when");
	NodeSet n_schedules = root->find("schedules/schedule");

	if (n_defaults.size() != 7) throw Error("not 7 defaults");

	//settings
	for (unsigned int i = 0; i < n_settings.size(); ++i)
	{
		const Glib::ustring nodename  = n_settings[i]->get_name();
		Node::NodeList list           = n_settings[i]->get_children();
		Node::NodeList::iterator iter = list.begin();

		if (list.size() == 0) throw Error("empty setting");

		const TextNode* nodeText = dynamic_cast<const TextNode*>(*iter);

		if (nodeText)
		{
			if      (nodename == "length")
				settings.length = ustring_to_int(nodeText->get_content());
			else if (nodename == "device")
				settings.device = nodeText->get_content().raw();
			else if (nodename == "start")
				settings.start  = from_undelimited_string(nodeText->get_content().raw());
			else if (nodename == "end")
				settings.end    = from_undelimited_string(nodeText->get_content().raw());
		}
	}

	//defaults
	for (unsigned int i = 0; i < n_defaults.size(); ++i)
	{
		Node::NodeList list           = n_defaults[i]->get_children();
		Node::NodeList::iterator iter = list.begin();

		if (list.size() == 0) continue;
		
		const TextNode* nodeText  = dynamic_cast<const TextNode*>(*iter);
		if (nodeText) defaults[i] = nodeText->get_content().raw();
	}

	//quiets
	add_whens(n_quiets, quiets);

	//overrides
	add_whens(n_overrides, overrides);

	//schedules
	for (unsigned int i = 0; i < n_schedules.size(); ++i)
	{
		schedule s;
		const Element* nodeSchedule = dynamic_cast<const Element*>(n_schedules[i]);

		//attributes
		const Attribute* id_attribute   = nodeSchedule->get_attribute("id");
		const Attribute* name_attribute = nodeSchedule->get_attribute("name");

		if (id_attribute)
			s.id = id_attribute->get_value().raw();
		else throw Error("id not specified");
		
		if (name_attribute)
			s.name = name_attribute->get_value().raw();
		else throw Error("name not specified");

		//times
		Element::NodeList children = nodeSchedule->get_children();

		for (Element::NodeList::iterator iter = children.begin(); iter != children.end(); ++iter)
		{
			Node::NodeList list           = (*iter)->get_children();
			Node::NodeList::iterator iter = list.begin();

			if (list.size() == 0) continue;

			const TextNode* nodeText  = dynamic_cast<const TextNode*>(*iter);

			if (nodeText)
			{
				vector<string> times = Split::split(nodeText->get_content().raw(), ":");

				if (times.size() != 2) throw Error("time not in 00:00 format");

				time t(string_to_int(times[0]), string_to_int(times[1]));
				s.times.push_back(t);
			}
		}

		schedules.push_back(s);
	}
}

void Config::add_whens(NodeSet& nodeset, vector<when>& whens)
{
	for (unsigned int i = 0; i < nodeset.size(); ++i)
	{
		const Element* nodeElement = dynamic_cast<const Element*>(nodeset[i]);
		Node::NodeList list           = nodeset[i]->get_children();
		Node::NodeList::iterator iter = list.begin();
		
		if (list.size() == 0) continue;

		const TextNode* nodeText = dynamic_cast<const TextNode*>(*iter);
		
		if (nodeText)
		{
			when w;
			vector<string> dates = Split::split(nodeText->get_content().raw(), "-");

			//start date
			w.start = from_undelimited_string(dates[0].substr(0,8));

			if (dates[0].length() == 12)
			{
				w.start_time.h = string_to_int(dates[0].substr(8, 2));
				w.start_time.m = string_to_int(dates[0].substr(10,2));
			}

			//end date
			if (dates.size() > 1)
			{
				w.end = from_undelimited_string(dates[1].substr(0,8));
			
				if (dates[1].length() == 12)
				{
					w.end_time.h = string_to_int(dates[1].substr(8, 2));
					w.end_time.m = string_to_int(dates[1].substr(10,2));
				}
				else
				{
					w.end_time.h = max_hours;
					w.end_time.m = max_minutes;
				}
			}
			
			//Start/end of a period during each of these days
			const Attribute* start_attribute = nodeElement->get_attribute("start");
			const Attribute* end_attribute   = nodeElement->get_attribute("end");

			if (start_attribute)
			{
				vector<string> times = Split::split(start_attribute->get_value().raw(), ":");

				if (times.size() != 2) throw Error("period start time not in 00:00 format");

				w.period_start.h = string_to_int(times[0]);
				w.period_start.m = string_to_int(times[1]);
			}

			if (end_attribute)
			{
				vector<string> times = Split::split(end_attribute->get_value().raw(), ":");
				
				if (times.size() != 2) throw Error("period end time not in 00:00 format");
				
				w.period_end.h = string_to_int(times[0]);
				w.period_end.m = string_to_int(times[1]);
			}
			else
			{
				w.period_end.h = max_hours;
				w.period_end.m = max_minutes;
			}

			//Execute
			const Attribute* exec_attribute = nodeElement->get_attribute("exec");

			if (exec_attribute)
				w.exec = exec_attribute->get_value().raw();

			whens.push_back(w);
		}
	}
}

ostream& operator<<(ostream& os, const Config::Settings& s)
{
	os << s.length << endl
	   << s.device << endl
	   << s.start  << endl
	   << s.end    << endl;

	return os;
}

ostream& operator<<(ostream& os, const Config::time& t)
{
	os << t.h << ":" << t.m;

	return os;
}

ostream& operator<<(ostream& os, const Config::when& w)
{
	os << w.exec         << endl
	   << w.start        << endl
	   << w.end          << endl
	   << w.start_time   << endl
	   << w.end_time     << endl
	   << w.period_start << endl
	   << w.period_end   << endl;
	
	return os;
}

ostream& operator<<(ostream& os, const Config::schedule& s)
{
	os << s.id   << endl
	   << s.name << endl;

	for (unsigned int i = 0; i < s.times.size(); ++i)
		os << "   " << s.times[i] << endl;

	return os;
}

ostream& operator<<(ostream& os, const Config& c)
{
	//settings
	os << c.settings << endl;

	//defaults
	for (unsigned int i = 0; i < c.defaults.size(); ++i)
		os << c.defaults[i] << endl;
	
	//quiets
	for (unsigned int i = 0; i < c.quiets.size(); ++i)
		os << c.quiets[i] << endl;

	//overrides
	for (unsigned int i = 0; i < c.overrides.size(); ++i)
		os << c.overrides[i] << endl;

	//schedules
	for (unsigned int i = 0; i < c.schedules.size(); ++i)
		os << c.schedules[i] << endl;
	
	return os;
}
