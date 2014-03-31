#!/bin/bash
PLUGIN=$1
ARGUMENTS="$2"

echo find: $ARGUMENTS on $PLUGIN
find plugins/$PLUGIN/*/ -maxdepth 1 -type f -exec grep "$ARGUMENTS"  {} + | awk -F ':' '{print $2}' | sed 's/^ *//g' | sed 's/^[ \t]*//g'
