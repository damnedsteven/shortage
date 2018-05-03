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

# to_addr = ['yi.li5@hpe.com']
to_addr = ['emcn.planning@hpe.com', 'cpmoissengineers@hpe.com', 'cpmo_essn_celestic@hpe.com', 'emcn.wh@hpe.com', 'mengyun.li@hpe.com', 'yanlin-mmsh.fei@hpe.com', 'mmsh.cto@mentormedia.com', 'cai-xiu_hu@mentormedia.com', 'yan-lin_fei@mentormedia.com', 'shirley_wang@mentormedia.com', 'meil@hpe.com', 'shirley-mmsh.wang@hpe.com', 'ipt@maitrox.com', 'taojun.sj@hpe.com', 'qin-wen.yao@hpe.com']
cc_addr = ['joy-m.huang@hpe.com']
bcc_addr = ['yi.li5@hpe.com']

smtp_server = 'smtp3.hpe.com'

query = """
	SELECT DISTINCT
		m.publish `Publish Time`,
		m.pfc `PF Category`,
		m.orderday `Order Date`,
		m.bkpl `BKPL Time`,
		m.rtp `RTP Time`,
		m.so `Sales Order`,
		m.so_item `Sales Order Item`,
		m.product `Product`,
		m.product_pl `Product PL`,
		m.bpo `BPO`,
		m.plo `PLO`,
		m.pn `Material Part No.`,
		p.is_overdue `Overdue?`,
		p.ctrl_id `Ctrl ID`,
		m.sales_area `Sales Area`,
		m.shortage_qty `Shortage QTY`,
		m.required_qty `Required QTY`,
		#m.filled_qty `Filled QTY`,
		p1.sum_arrival_qty `Supp.Q`,
		p1.eta `ETA`,
		p.remark `Remarks`,
		shortage_reason.name `Shortage Reason (Category)`,
		m.received `抵达时间`,
		date_format(m.lastupdated, "%b %d %Y %h:%i %p") `Updated`
	FROM 
		master m 
		LEFT JOIN 
		(	
			SELECT pn, eta, SUM(arrival_qty) sum_arrival_qty, MIN(is_copy) is_copy
			FROM pn
			WHERE status = 1
			GROUP BY pn, eta
		) p1
		ON m.pn = p1.pn
		LEFT JOIN
		pn p
		ON p1.pn = p.pn AND p1.is_copy = p.is_copy
		LEFT JOIN
		shortage_reason
		ON p.id_shortage_reason=shortage_reason.id
	WHERE 
		m.status="1"
"""

text = """\
<html>
  <head></head>
  <body>
    <p>Hi all,<br><br>
       Here is the latest material shortage status. Pls let <a href="mailto:taojun.sj@hpe.com">SJ, Taojun (EMCN Warehouse)</a> know if there is any wrong information.  Thanks for your attention!<br>
       <br>请登录网页版缺料显示系统： <a href="http://16.187.228.117/shortage/planner/">网址</a> 
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
# msg['To'] = _format_addr('admin <%s>' % to_addr)
msg['To'] = ", ".join(to_addr)
msg['CC'] = ", ".join(cc_addr)
msg['Subject'] = Header('for Planner - ESSN material shortage (%s)' % (to_date), 'utf-8').encode()

to_addrs = to_addr + cc_addr + bcc_addr

server = smtplib.SMTP(smtp_server, 25)
server.set_debuglevel(1)
#server.login(from_addr, password)
server.sendmail(from_addr, to_addrs, msg.as_string())
server.quit()
