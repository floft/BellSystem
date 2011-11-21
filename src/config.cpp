#include "config.h"

int Config::ustring_to_int(const Glib::ustring& input) const
{
	int output = -1;
	stringstream s;
	s << input.raw();
	s >> output;
	
	return output;
}

int Config::string_to_int(const string& input) const
{
	int output = -1;
	stringstream s;
	s << input;
	s >> output;

	return output;
}

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
	for (int i = 0; i < n_settings.size(); ++i)
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
	for (int i = 0; i < n_defaults.size(); ++i)
	{
		Node::NodeList list           = n_defaults[i]->get_children();
		Node::NodeList::iterator iter = list.begin();

		if (list.size() == 0) continue;
		
		const TextNode* nodeText  = dynamic_cast<const TextNode*>(*iter);
		if (nodeText) defaults[i] = nodeText->get_content().raw();
	}

	//quiets
	for (int i = 0; i < n_quiets.size(); ++i)
	{
		Node::NodeList list           = n_quiets[i]->get_children();
		Node::NodeList::iterator iter = list.begin();
		
		if (list.size() == 0) continue;

		const TextNode* nodeText = dynamic_cast<const TextNode*>(*iter);
		
		if (nodeText)
		{
			when w;
			vector<string> dates = Split::split(nodeText->get_content().raw(), "-");

			//start date
			w.start = from_undelimited_string(dates[0].substr(0,8));
			

			/*
			 *
			 *		INSERT ATRIBUTE CODE HERE
			 *
			 *
			 */

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
			}

			quiets.push_back(w);
		}
	}
	
}

void recursive(const Node* node)
{
	//const TextNode*     nodeText    = dynamic_cast<const TextNode*>(node);
	//const Element*      nodeElement = dynamic_cast<const Element*>(node);
	//const Glib::ustring nodename    = node->get_name();
}
