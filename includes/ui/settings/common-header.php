<html>
	<head>
		<?php wp_head(); ?>
		<style type="text/css">
		  html.wp-toolbar {
		      margin-top: 0px !important;
		      padding-top : 0px important;
		  }
		  .wp-submenu {
		      margin-left: 0px !important;
		  }
		</style>
	</head>
	<body style="">
		<div class="modal fade" id="scan_modal" tabindex="-1" role="dialog" aria-hidden="true">
		  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
		    <div class="modal-content">
		      <div class="modal-header">
		        <h5 class="modal-title">Scanning <span id="modal_site_name" class="badge badge-info" style="font-size: 14px;"></span></h5>
		        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
		          <span aria-hidden="true">&times;</span>
		        </button>
		      </div>
		      <div class="modal-body">
		      		<div class="row w-100 p-2 mb-2 mt-2 border-1 shadow-sm">
		      			<div class="col-6">
		      				<span>Scanning <b>Pages</b></span>
		      			</div>
		      			<div class="col-6 d-flex justify-content-center">
		      				<div id="page_scan_result"><b>Waiting..</b></div>
		      			</div>
		      		</div>
		      		<div class="row w-100 p-2 mb-2 mt-2 border-1 shadow-sm">
		      			<div class="col-6">
		      				<span>Scanning <b>Posts</b></span>
		      			</div>
		      			<div class="col-6 d-flex justify-content-center">
		      				<div id="post_scan_result"><b>Waiting..</b></div>
		      			</div>
		      		</div>
		      		<div class="row w-100 p-2 mb-2 mt-2 border-1 shadow-sm">
		      			<div class="col-6">
		      				<span>Scanning <b>Products</b></span>
		      			</div>
		      			<div class="col-6 d-flex justify-content-center">
		      				<div id="product_scan_result"><b>Waiting..</b></div>
		      			</div>
		      		</div>
		      </div>
		      <div class="modal-footer">
		        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
		      </div>
		    </div>
		  </div>
		</div>
	
		<section class="p-1">
        		<div class="row w-100 m-0 justify-content-center">
        			<div class="container">
        				<div class="row p-0 w-100 m-0 justify-content-center">
        					<div class="container-fluid">
		
