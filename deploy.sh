#!/bin/bash
git add -A
git commit
git push
ssh b 'cd bellsystem-git; makepkg -sif'
