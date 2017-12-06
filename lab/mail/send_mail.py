#!/usr/bin/env python3
# -*- coding: utf-8 -*-

from email import encoders
from email.header import Header
from email.mime.text import MIMEText
from email.utils import parseaddr, formataddr
from email.mime.multipart import MIMEMultipart, MIMEBase
import smtplib
from datetime import datetime, timedelta

import MySQLdb

import os
my_path = os.path.dirname(os.path.abspath(__file__))

import sys  

reload(sys)  
sys.setdefaultencoding('utf8')


def _format_addr(s):
    name, addr = parseaddr(s)
    return formataddr((Header(name, 'utf-8').encode(), addr))

def query_mysql(query):
	# Get data from 200 DB
	conn = MySQLdb.connect("16.187.230.200", "yi", "asdfqwer", "shortage", charset = 'utf8')
	cursor = conn.cursor()	
	cursor.execute(query)
	#get header and rows
	header = [i[0] for i in cursor.description]
	rows = [list(i) for i in cursor.fetchall()]
	#append header to rows
	rows.insert(0,header)
	cursor.close()
	conn.close()
	return rows

#take list of lists as argument	
def nlist_to_html(list2d):
	#bold header
	htable=u'<table border="1" bordercolor=000000 cellspacing="0" cellpadding="1" style="table-layout:fixed;vertical-align:bottom;font-size:13px;font-family:verdana,sans,sans-serif;border-collapse:collapse;border:1px solid rgb(130,130,130)" >'
	list2d[0] = [u'<b>' + i + u'</b>' for i in list2d[0]] 
	#
	for row in list2d:
		newrow = u'<tr>' 
		newrow += u'<td align="left" style="padding:1px 4px">'+unicode(row[0])+u'</td>'
		row.remove(row[0])
		newrow = newrow + ''.join([u'<td align="right" style="padding:1px 4px">' + unicode(x or "") + u'</td>' for x in row])  
		newrow += '</tr>' 
		htable+= newrow
	htable += '</table>'
	return htable
	

def sql_html(query):
	return nlist_to_html(query_mysql(query))
	
now = datetime.now()
earlier = now - timedelta(hours=12)
# from_date = earlier.strftime('%y') + '/' + earlier.strftime('%m') + '/' + earlier.strftime('%d') + '-' + earlier.strftime('%H')
to_date = now.strftime('%y') + '/' + now.strftime('%m') + '/' + now.strftime('%d') + '-' + now.strftime('%H')
	
from_addr = 'shortage@emcn.cn'

to_addr = ['yi.li5@hpe.com']

cc_addr = ['yi.li5@hpe.com']

bcc_addr = ['brooklynburgerking@gmail.com']

# to_addr = ['yi.li5@hpe.com', 'cpmo-iss-buyer@hpe.com', 'zhou@hpe.com', 'cpmo-iss-planner@hpe.com', 'taojun.sj@hpe.com', 'joy-m.huang@hpe.com', 'hai-chuan.zhao@hpe.com', 'ivy.y.lin@hpe.com']

smtp_server = 'smtp3.hpe.com'

query = """
	SELECT 
		is_copy `Copy#`,
		pn `Part No.`,
		ctrl_id `Ctrl ID`,
		buyer_name `Buyer`,
		shortage_qty `TTL-S`,
		pline_shortage_qty `S-RAW`,
		passthru_shortage_qty `S-OPT`,
		earliest_bkpl `Earliest BKPL Time`,
		arrival_qty `Supp.Q`,
		eta `ETA`,
		CASE
			WHEN slot = '0' THEN 'morning'
			WHEN slot = '1' THEN 'afternoon'
			WHEN slot = '2' THEN 'night'
		END `Slot`,
		remark `Remark`,
		carrier.name `Carrier`,
		judge_supply `Judge Supply?`,
		shortage_reason.name `Shortage Reason (Category)`,
		shortage_reason_detail `Shortage Reason (Comments)`,
		bill_number `HAWB`,
		date_format(lastupdated, "%b %d %Y %h:%i %p") `Updated`
	FROM 
		pn 
		LEFT JOIN 
		carrier
		ON pn.id_carrier=carrier.id
		LEFT JOIN
		shortage_reason
		ON pn.id_shortage_reason=shortage_reason.id
	WHERE (status=1 OR is_copy = -1) AND received IS NULL 
	ORDER BY pn
"""

text = """\
<html>
  <head></head>
  <body>
    <p>Hi all,<br><br>
       Here is the latest material shortage status, pls check and fill in the ETA schedule asap. Pls let <a href="mailto:taojun.sj@hpe.com">SJ, Taojun (EMCN Warehouse)</a> know if there is any wrong information.  Thanks for your attention!<br>
       <br>请登录网页版缺料显示系统： <a href="http://16.187.228.117/shortage/buyer/">网址</a> 
    </p>
	<br>
  </body>
</html>
"""

table = sql_html(query)

text2 = """\
<html>
  <head></head>
  <body>
    <p><br>Thanks & Best Regs.<br>
       cpmo ESSN warehouse system<br>
	   Tel: 862120510334
    </p>
	<br>
  </body>
</html>
"""

msg = MIMEMultipart()
	
# 邮件正文是MIMEText:
msg.attach(MIMEText(text+table+text2, 'html', 'utf-8'))


msg['From'] = _format_addr('Shortage Alert <%s>' % from_addr)
# msg['To'] = _format_addr('recipient <%s>' % ",".join(to_addr))
msg['To'] = ", ".join(to_addr)
# msg['CC'] = _format_addr('admin <%s>' % ",".join(cc_addr))
msg['CC'] = ", ".join(cc_addr)
msg['BCC'] = ", ".join(bcc_addr)
msg['Subject'] = Header('for Buyer - ESSN material shortage (%s)' % (to_date), 'utf-8').encode()

to_addrs = to_addr + cc_addr + bcc_addr

server = smtplib.SMTP(smtp_server, 25)
server.set_debuglevel(1)
#server.login(from_addr, password)
server.sendmail(from_addr, to_addrs, msg.as_string())
server.quit()
