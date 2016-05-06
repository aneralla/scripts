
>post.json

awk -vDate=`date -d'now-30 minutes' +[%d/%b/%Y:%H:%M:%S` '$7 > Date {print Date, $0}' /var/log/httpd/dev-appletv.ott.aetnd.com_access.log | grep -v "200" > /tmp/log
len=`cat /tmp/log | wc -l`
a=1

tim_temp=""

while [ $a -le $len ]
do

>post.json
   x=`cat /tmp/log | awk '{FS=" "; print $12}' | head -$a| tail -1`

   url=$x

   resp=`cat /tmp/log | awk '{FS=" "; print $14}'| head -$a | tail -1`

   stack='appletv'

   tim=`cat /tmp/log | awk '{FS=" "; print $1}' | sed 's/\[//g' | tail -1`

   a=`expr $a + 1`

   echo "{" >> post.json
   echo "\"url\":\"$url\"," >> post.json
   echo "\"http_response_code\":\"$resp\"," >> post.json
   echo "\"stack\":\"$stack\"," >> post.json
   echo "\"time\":\"$tim\"" >> post.json
   echo "}" >> post.json

if [ "$tim" != "$tim_temp" ]
   then
      curl -v -X POST "http://ec2-54-86-253-113.compute-1.amazonaws.com/write.php" -d @post.json
      tim_temp=$tim
fi

done
