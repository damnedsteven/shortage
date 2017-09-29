#!/usr/bin/env python3
# -*- coding: utf-8 -*-

from email import encoders
from email.header import Header
from email.mime.text import MIMEText
from email.utils import parseaddr, formataddr
from email.mime.multipart import MIMEMultipart, MIMEBase
import smtplib
from write_message import write_message
from datetime import datetime, timedelta

import os
my_path = os.path.dirname(os.path.abspath(__file__))

def _format_addr(s):
    name, addr = parseaddr(s)
    return formataddr((Header(name, 'utf-8').encode(), addr))

now = datetime.now()
earlier = now - timedelta(hours=12)
# from_date = earlier.strftime('%y') + '/' + earlier.strftime('%m') + '/' + earlier.strftime('%d') + '-' + earlier.strftime('%H')
to_date = now.strftime('%y') + '/' + now.strftime('%m') + '/' + now.strftime('%d') + '-' + now.strftime('%H')
	
from_addr = 'shortage@emcn.com'
to_addr = ['yi.li5@hpe.com']
# to_addr = ['yi.li5@hpe.com', 'yuanfan.shi@hpe.com', 'Steven-zhang.cls@hpe.com', 'gengwu-chen.cls@hpe.com', 'lingcongli.cls@hpe.com', 'wenjie-zhang.cls@hpe.com', 'cls.xuhua-yang@hpe.com', 'egordercheck.cls@hpe.com', 'line-syb.cls@hpe.com', 'qiong-zhang.cls@hpe.com', 'huifen-cao.cls@hpe.com', 'peter-chen.cls@hpe.com', 'emcn.wh@hpe.com', 'minminz@hpe.com', 'jipingz@hpe.com', 'hai-chuan.zhao@hpe.com']

smtp_server = 'smtp3.hpe.com'


URL_MR = 'http://16.187.229.14/emcn/PCTWHView/index.php'

URL_P = 'http://16.187.229.14/emcn/PDeck/index.php'

URL_PGI = 'http://16.187.229.14/emcn/PCTWHView/index.php'

URL_PC = 'http://16.187.229.14/emcn/PCTOverview/index.php'

WD = (0, 1, 2, 3, 4)
WH = [(0.0, 23.99)]

Target_MR = 24
Target_P = 42
Target_PGI = 6
Target_PC = {'CTO' : 72, 'PPS Option' : 48, 'Complex CTO' : 96, 'Rack' : 96, 'Others' : 96}

Table_MR = write_message(Type = 'MR', Target = Target_MR, From = 'BirthDate', To = 'WHUpdateTime', WorkingDay = WD, WorkingHour = WH, URL = URL_MR, Shift = from_date)
Table_P = write_message(Type = 'P', Target = Target_P, From = 'WHUpdateTime', To = 'HandoverTime', WorkingDay = WD, WorkingHour = WH, URL = URL_P, Shift = from_date)
Table_PGI = write_message(Type = 'PGI', Target = Target_PGI, From = 'HandoverTime', To = 'PGITime', WorkingDay = WD, WorkingHour = WH, URL = URL_PGI, Shift = from_date)
Table_PC = write_message(Type = 'PC', Target = Target_PC, From = 'BirthDate', To = 'PGITime', WorkingDay = WD, WorkingHour = WH, URL = URL_PC, Shift = from_date)

make_plot(Shift = from_date)

Plot = """
	<table border="1" width="888">
		<tr>
			<td>
				<img src="cid:0" alt="PCTFailureReport" align="center" border=0 />
			</td>
		</tr>
		</tr>
	</table>
"""
# msg = MIMEText(Table_MR + Table_P + Table_PGI + Table_PC, 'html', 'utf-8')

msg = MIMEMultipart()

# 添加附件就是加上一个MIMEBase，从本地读取一个图片:
with open(my_path + '/img/plot.png', 'rb') as f:
    # 设置附件的MIME和文件名，这里是png类型:
    mime = MIMEBase('image', 'png', filename='plot.png')
    # 加上必要的头信息:
    mime.add_header('Content-Disposition', 'attachment', filename='plot.png')
    mime.add_header('Content-ID', '<0>')
    mime.add_header('X-Attachment-Id', '0')
    # 把附件的内容读进来:
    mime.set_payload(f.read())
    # 用Base64编码:
    encoders.encode_base64(mime)
    # 添加到MIMEMultipart:
    msg.attach(mime)
	
# 邮件正文是MIMEText:
msg.attach(MIMEText(Plot + Table_MR + Table_P + Table_PGI + Table_PC, 'html', 'utf-8'))


msg['From'] = _format_addr('Shortage Alert <%s>' % from_addr)
msg['To'] = _format_addr('Planner Admin <%s>' % to_addr)
msg['Subject'] = Header('Material Shortage Alert (%s)' % (to_date), 'utf-8').encode()

server = smtplib.SMTP(smtp_server, 25)
server.set_debuglevel(1)
#server.login(from_addr, password)
server.sendmail(from_addr, to_addr, msg.as_string())
server.quit()
