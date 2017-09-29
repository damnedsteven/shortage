from __future__ import division
import MySQLdb

def write_message(Type, Target, From, To, WorkingDay, WorkingHour, URL, Shift):
	html = ""

	# Get data from 200 DB
	conn = MySQLdb.connect("16.187.230.200", "yi", "asdfqwer", "shortage")
	cursor = conn.cursor()
	
	cursor.execute("""
	SELECT m.*, date_format(orderdate, "%d/%m/%Y") as orderdate, date_format(m.lastupdated, "%b %d %Y %h:%i %p") as lastupdated, p1.*, p.*
	FROM master m 
	LEFT JOIN 
	(	
		SELECT pn, eta, SUM(arrival_qty) sum_arrival_qty, MIN(is_copy) is_copy
		FROM pn
		GROUP BY pn, eta
	) p1
	ON m.pn = p1.pn
	LEFT JOIN
	pn p
	ON p1.pn = p.pn AND p1.is_copy = p.is_copy
	WHERE m.status="1"
	""")

	if cursor.rowcount != 0:
		
		# Generate table
		if (Type == 'MR'):
			html += """
			<table border="1" width="888">
				<tr bgcolor="#1F77B4">
					<th colspan="4">备料(MR) TAT Performance</th>
				</tr>
				<tr bgcolor="#1F77B4">
					<th width="30%">Platform</th>
					<th width="20%">MR PLO QTY</th>
					<th width="30%">MR TAT Fail PLO QTY (Over 24H)</th>
					<th width="20%">Failure Rate</th>
				</tr>
		
		# for k, v in Count.items():
		for v in Sorted_PF:
			if Type == 'PC' and v == 'CTO':
				html += '<tr bgcolor=\'#D6CF27\'>'
			else:
				html += '<tr>'
			html += '<td>' + v + '</td>'
			html += '<td>' + str(Count[v]['Total']) + '</td>'
			if (Count[v]['Fail'] > 0):	
				html += '<td bgcolor=\'#FFC7CE\'><a href=' + URL + '?PLO=' + FailedPLOs[v] + '>' + str(Count[v]['Fail']) + '</a></td>'
			else:
				html += '<td>' + str(Count[v]['Fail']) + '</td>'
			html += '<td>' + "{:.0%}".format(Count[v]['Failure_Rate']) + '</td>'
			html += '</tr>'
			
		if (Type == 'MR'):
			html += '<tr bgcolor=\'#1F77B4\'>'
		elif (Type == 'P'):
			html += '<tr bgcolor=\'#FF7F0E\'>'
		elif (Type == 'PGI'):
			html += '<tr bgcolor=\'#2CA02C\'>'
		else:
			html += "<tr>"
			
		html += """
				<th>Total</th>
				<th>{Sum}</th>
				<th>{Sum_Fail}</th>
				<th>{Failure_Rate}</th>
			</tr>
		</table>
		""".format(Sum = Sum, Sum_Fail = Sum_Fail, Failure_Rate = "{:.0%}".format(Failure_Rate))
	
	conn.commit()
	conn.close()
	
	return html