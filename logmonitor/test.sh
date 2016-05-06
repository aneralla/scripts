>post.json

#awk -vDate=`date -d'now-30 minutes' +[%d/%b/%Y:%H:%M:%S` '$7 > Date {print Date, $0}' /var/log/httpd/dev.mediaservice.aetndigital.com_access.log | grep -v "200" > /tmp/log

cp -p /var/log/httpd/dev.mediaservice.aetndigital.com_access-1.log tmplog1

cat tmplog1 | grep -v " 200 " | grep -v " 408 " > tmplog

>/var/log/httpd/dev.mediaservice.aetndigital.com_access-1.log

len=`cat tmplog | wc -l`
a=1

#tim_temp=""

while [ $a -le $len ]
do

>post.json
   x=`cat tmplog | awk '{FS=" "; print $7}' | head -$a| tail -1`

   url=$x

   resp=`cat tmplog | awk '{FS=" "; print $9}'| head -$a | tail -1`

   stack='mediaservice'

   en=$aetn_env

   tim=`cat tmplog | awk '{FS=" "; print $4}' | sed 's/\[//g' | tail -1`

   a=`expr $a + 1`

   echo "{" >> post.json
   echo "\"url\":\"$url\"," >> post.json
   echo "\"http_response_code\":\"$resp\"," >> post.json
   echo "\"stack\":\"$stack\"," >> post.json
   echo "\"env\":\"$en\"," >> post.json
   echo "\"time\":\"$tim\"" >> post.json
   echo "}" >> post.json

#if [ "$tim" != "$tim_temp" ]
#   then
      curl -v -X POST "http://ec2-54-86-253-113.compute-1.amazonaws.com/write.php" -d @post.json
#      tim_temp=$tim
#fi

done
