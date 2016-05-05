#!/bin/sh



awk -vDate=`date -d'now-30 minutes' +[%d/%b/%Y:%H:%M:%S` '$7 > Date {print Date, $0}' /var/log/httpd/dev-appletv.ott.aetnd.com_access.log | grep -v "200" > /tmp/log


len=`cat /tmp/log | wc -l`



a=1

while [ $a -le $len ]
do


   x=`cat /tmp/log | awk '{FS=" "; print $12}' | head -$a`
   url_$a=$x 
   resp_$a=`cat /tmp/log | awk '{FS=" "; print $14}'| head -$a`
   stack_$a='appletv'
   a=`expr $a + 1`
   
   echo "{" >> /tmp/post
   echo "\"url\":\"$url_$a\"," >> /tmp/post
   echo "\"http_response_code\":\"$resp_$a\"," >> /tmp/post
   echo "\"stack\":\"$stack_$a\"" >> /tmp/post


done

