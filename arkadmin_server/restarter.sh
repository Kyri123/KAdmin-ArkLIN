#!/bin/bash
# *******************************************************************************************
# @author:  Oliver Kaufmann (Kyri123)
# @copyright Copyright (c) 2019-2021, Oliver Kaufmann
# @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
# Github: https://github.com/Kyri123/Arkadmin
# *******************************************************************************************
# Parameter
WEBDIR="$1"
SCREENNAME="$2"
LOGFILE="$3" 

echo "Restart...." > $LOGFILE
cd $WEBDIR/arkadmin_server/
screen -S $SCREENNAME -p 0 -X quit >> $LOGFILE
npm install --force >> $LOGFILE
npm update >> $LOGFILE
npm fund >> $LOGFILE
screen -mdR $SCREENNAME ./start.sh
screen -wipe
echo "Done" >> $LOGFILE
exit