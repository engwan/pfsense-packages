pfctl -t ipblocklist -T kill
sed -i -e '/ipblocklist/d' /tmp/rules.debug

#ipfw -f -q flush (Version 0.1.4)
ls lists > file_list.txt
filelist="file_list.txt"

#READ contents in file_list.txt and process as file
for fileline in $(cat $filelist); do
iplist="lists/$fileline"
iplistout="lists/ipfw.ipfw"
perl convert.pl $iplist $iplistout
done
#echo "ipfw made"

#clean up ipfw.ipfw (duplicates)
rm lists/ipfw.ipfwTEMP
sort lists/ipfw.ipfw | uniq -u >> lists/ipfw.ipfwTEMP
mv lists/ipfw.ipfwTEMP lists/ipfw.ipfw
#echo "ipfw clean"



#Now edit /tmp/rules.debug

#find my line for table
export i=`grep -n 'block quick from any to <snort2c>' /tmp/rules.debug | grep -o '[0-9]\{2\}'`
export t=`grep -n 'User Aliases' /tmp/rules.debug |grep -o '[0-9]'`

i=$(($i+'1'))
t=$(($t+'1'))
#echo $i
#echo $t

rm /tmp/rules.debug.tmp

#Insert table-entry limit 
sed -i -e '/900000/d' /tmp/rules.debug
while read line
	do a=$(($a+1)); 
	#echo $a;
	if [ "$a" = "$t" ]; then
		echo "" >> /tmp/rules.debug.tmp
		echo "set limit table-entries 900000" >> /tmp/rules.debug.tmp
	fi
	echo $line >> /tmp/rules.debug.tmp
done < "/tmp/rules.debug"

mv /tmp/rules.debug /tmp/rules.debug.old
mv /tmp/rules.debug.tmp /tmp/rules.debug

pfctl -o basic -f /tmp/rules.debug > errorOUT.txt 2>&1

rm /tmp/rules.debug.tmp
#Insert ipblocklist rules
a="0"
echo $a
while read line
	do a=$(($a+1));
	echo $a; 
	if [ "$a" = "$i" ]; then
		echo "" >> /tmp/rules.debug.tmp
		echo "#ipblocklist" >> /tmp/rules.debug.tmp
		echo "table <ipblocklist> persist file '/usr/local/www/packages/ipblocklist/lists/ipfw.ipfw'" >> /tmp/rules.debug.tmp
		echo "block quick from <ipblocklist> to any label 'IP-Blocklist'" >> /tmp/rules.debug.tmp
		echo "block quick from any to <ipblocklist> label 'IP-Blocklist'" >> /tmp/rules.debug.tmp
	fi
	echo $line >> /tmp/rules.debug.tmp
done < "/tmp/rules.debug"

mv /tmp/rules.debug /tmp/rules.debug.old
mv /tmp/rules.debug.tmp /tmp/rules.debug

#Now execute the ipfw list (Take a long time in old version)
#sh lists/ipfw.ipfw (Version 0.1.4)
rm errorOUT.txt
pfctl -o basic -f /tmp/rules.debug > errorOUT.txt 2>&1
