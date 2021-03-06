<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<!--
/*
 * examples/mysql/index.html
 * 
 * This file is part of EditableGrid.
 * http://editablegrid.net
 *
 * Copyright (c) 2011 Webismymind SPRL
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://editablegrid.net/license
 */
 ?php require "login/loginheader.php"; ?>
-->



<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>ESSN Material Shortage</title>
		<link rel="stylesheet" href="css/style.css" type="text/css" media="screen">
		<link rel="stylesheet" href="css/responsive.css" type="text/css" media="screen">

        <link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css" />
		<link rel="stylesheet" href="css/font-awesome-4.1.0/css/font-awesome.min.css" type="text/css" media="screen">
        	</head>
	
	<body>
		<div id="wrap">
		<h1>ESSN Material Shortage<a href="/shortage/lab/mail/mail.php"><i class="fa fa-sign-out">&nbsp;&nbsp;&nbsp;&nbsp;Send Mail</i></a></h1> 
		
			<p><a href="/shortage/">Summary</a> | <a href="/shortage/planner/">Planner</a> | <a href="/shortage/buyer/">Buyer</a> | <a href="/shortage/kwe/">KWE</a> | <a href="/shortage/wh/">WH</a></p>
		
			<!-- Feedback message zone -->
			<div id="message"></div>

            <div id="toolbar">
              <input type="text" id="filter" name="filter" placeholder="Filter: type any text here"  onkeyup='saveValue(this);'/>
              <!-- <a id="showaddformbutton" class="button green"><i class="fa fa-plus"></i> Add new row</a> -->
            </div>
			
			<!-- Grid contents -->
			<div id="tablecontent"></div>
		
			<!-- Paginator control -->
			<div id="paginator"></div>
		</div>  
		
		<script src="js/editablegrid-2.1.0-b25.js"></script>   
		<script src="js/jquery-1.11.1.min.js" ></script>
        <!-- EditableGrid test if jQuery UI is present. If present, a datepicker is automatically used for date type -->
        <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js"></script>
		<script src="js/master.js" ></script>

		<script type="text/javascript">
		
            var datagrid = new DatabaseGrid();
			window.onload = function() { 

                // key typed in the filter field
                $("#filter").keyup(function() {
                    datagrid.editableGrid.filter( $(this).val());

                    // To filter on some columns, you can set an array of column index 
                    //datagrid.editableGrid.filter( $(this).val(), [0,3,5]);
                  });

                $("#showaddformbutton").click( function()  {
                  showAddForm();
                });
                $("#cancelbutton").click( function() {
                  showAddForm();
                });

                $("#addbutton").click(function() {
                  datagrid.addRow();
                });

        
			}; 
			
			document.getElementById("filter").value = getSavedValue("filter");   // set the value to this input
			/* Here you can add more inputs to set value. if it's saved */

			//Save the value function - save it to localStorage as (ID, VALUE)
			function saveValue(e){
				var id = e.id;  // get the sender's id to save it . 
				var val = e.value; // get the value. 
				localStorage.setItem(id, val);// Every time user writing something, the localStorage's value will override . 
			};

			//get the saved value function - return the value of "v" from localStorage. 
			function getSavedValue  (v){
				if (localStorage.getItem(v) === null) {
					return "";// You can change this to your defualt value. 
				}
				return localStorage.getItem(v);
			};
		
		</script>

        <!-- simple form, used to add a new row -->
        <div id="addform">

            <div class="row">
                <input type="text" id="pn" name="pn" placeholder="pn" />
            </div>

             <div class="row">
                <input type="text" id="shortage_qty" name="shortage_qty" placeholder="shortage_qty" />
            </div>

            <div class="row tright">
              <a id="addbutton" class="button green" ><i class="fa fa-save"></i> Apply</a>
              <a id="cancelbutton" class="button delete">Cancel</a>
            </div>
        </div>
        
	</body>

</html>
