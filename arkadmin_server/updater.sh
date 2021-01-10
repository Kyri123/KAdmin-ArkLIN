#!/bin/bash
# *******************************************************************************************
# @author:  Oliver Kaufmann (Kyri123)
# @copyright Copyright (c) 2019-2021, Oliver Kaufmann
# @license MIT License (LICENSE or https://github.com/Kyri123/KAdmin-ArkLIN/blob/master/LICENSE)
# Github: https://github.com/Kyri123/KAdmin-ArkLIN
# *******************************************************************************************
# Parameter 
WEBDIR="$1"
SCREENNAME="$2"
BRANCHE="$3"
LOGFILE="$4" 

echo "Update...." > $LOGFILE
rm -R tmp
mkdir tmp
cd tmp
wget https://github.com/Kyri123/KAdmin-ArkLIN/archive/$BRANCHE.zip >> $LOGFILE
unzip $BRANCHE.zip >> $LOGFILE
rm $BRANCHE.zip
cd KAdmin-ArkLIN-$BRANCHE
screen -S $SCREENNAME -p 0 -X quit >> $LOGFILE
sleep 2s
rm -R ./arkadmin_server/config
rm -R ./install
rm -R ./install
rm ./install.php
rm ./arkadmin_server/data/restarter.log
rm ./arkadmin_server/data/updater.log
rm ./arkadmin_server/data/sha.txt
rm ./arkadmin_server/data/server.log
yes | cp -rf ./ $WEBDIR/ >> $LOGFILE
cd ../../
rm -R tmp
cd $WEBDIR/arkadmin_server/
npm install --force >> $LOGFILE
npm update >> $LOGFILE
npm fund >> $LOGFILE
chmod 777 -R ./../
screen -mdS $SCREENNAME ./start.sh
screen -wipe
echo "Done" >> $LOGFILE
exit