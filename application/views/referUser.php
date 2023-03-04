<!DOCTYPE html>
<html>
	<head>
		<title>Go Solar</title>
	<meta charset="utf-8">
    <link rel="shortcut icon" type="image/ico" href="favicon.ico">	
	<meta property="og:site_name" content="Go Solar" />
    <meta property="og:type" content="website" />
    <meta property="og:title" content="Go Solar" />
    <meta property="og:url" content="<?php echo base_url() ?>" />
    <meta property="og:image" content="<?php echo base_url() ?>app/image.png" />
    <meta property="og:description" content="Click to receive a Complimentary Solar Savings Plan!" />
    <!-- Twitter Card data -->
    <meta name="twitter:description" content="Click to receive a Complimentary Solar Savings Plan!">
    <!-- Default meta description -->
    <meta name="description" content="Click to receive a Complimentary Solar Savings Plan!">
    <!-- Twitter Card data -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:site" content="Go Solar">
    <meta name="twitter:title" content="Go Solar">
    <meta name="twitter:image" content="<?php echo base_url() ?>app/image.png">
	</head>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url() ?>app/bower_components/bootstrap/dist/css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="<?php echo base_url() ?>app/assets/css/AdminLTE.min.css">
	<script type="text/javascript" src="<?php echo base_url() ?>app/bower_components/jquery/dist/jquery.min.js"></script>
	<script type="text/javascript" src="<?php echo base_url() ?>app/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>	
	<script type="text/javascript" src="<?php echo base_url() ?>assets/jquery-validation/dist/jquery.validate.js"></script>

	<style type="text/css">
		.error {
			color: red;
    		font-weight: 400;
		}
	</style>	
	<body>
	<aside class="left-aside">
		<!-- <section class="content-header">
	        <h1>
	            Add Area
	        </h1>
   		</section> -->
   		<section class="content">
			<form role="form" id="addRef" name="addRef" action="<?php echo base_url().'index.php/api/addWebReferal';?>" method="post">
			    <div class="row">
			    	<div class="col-md-4"></div>                        
			        <div class="col-md-4">
			            <div class="box box-primary">
			                <div class="box-header">
			                    <h3 class="box-title">Go Solar is the nationwide leader in a $0 upfront solar purchase.  Receive a complimentary Solar Savings Plan today!</h3>
			                </div>
			                <div class="box-body"> 
			                	<input type="hidden" name="referred_by" value="<?php echo $referred_by; ?>">                       
			                    <div class="form-group">
			                      	<label>Firstname:</label>
			                      	<input class="form-control required" type="text" name="first_name" placeholder="First Name" min-length="3" maxlength="20">
			                   </div>
			                   <div class="form-group">
			                      	<label>Lastname:</label>
			                      	<input class="form-control required" type="text" name="last_name" placeholder="Last Name" min-length="3" maxlength="20">
			                   </div>
			                   <div class="form-group">
			                      	<label>Email:</label>
			                      	<input class="form-control required" type="email" name="email" placeholder="email">
			                   </div>
			                   <div class="form-group">
			                      	<label>Phone:</label>
			                      	<input class="form-control required" type="number" name="phone" placeholder="Phone" minlength="8" maxlength="10">
			                   </div>
			                   <div class="form-group">
			                      	<label>Address:</label>
			                      	<input class="form-control required" type="text" name="address" placeholder="Address" maxlength="16">
			                   </div>
			                   <div class="form-group">
			                      	<label>City:</label>
			                      	<input class="form-control required" type="text" name="city" placeholder="City" maxlength="16">
			                   </div>
			                   <div class="form-group">
			                      	<label>State:</label>
			                      	<input class="form-control required" type="text" name="state" placeholder="State" maxlength="16">
			                   </div>
			                    <div align="center">	
			                        <button type="submit" class="btn btn-primary">Submit</button>
			                    </div>
			                </div>
			           </div>
			        </div>
			        <div class="col-md-4"></div>
			    </div>
			</form>	   			
   		</section>
	</aside>		
	</body>
</html>
<script type="text/javascript">
	$("#addRef").validate({
		rules:{
			first_name:{
               	required:true,
               	minlength:3,
               	maxlength:20
            },
            last_name:{
               	required:true,
               	minlength:3,
               	maxlength:20
            },
            email:{
            	required:false,
            	email:true,
            	remote:{
                    url:"<?php echo site_url()?>index.php/admin_api/checkEmail",
                    type:"post"                    
                }            	
            },
            phone:{
            	required: true,
            	minlength:8,
            	maxlength:10,
            	remote:{
                    url:"<?php echo site_url()?>index.php/admin_api/checkPhone",
                    type:"post"                    
                }
            },
            city:{
            	required: false,
            	maxlength: 10
            },
            address:{
            	required:false,
            	maxlength: 200
            },
            state:{
            	required:false,
            	maxlength: 20
            }
		},

		messages:{
			first_name:{
                required:"Please Enter Firstname",
                minlength:"Firsname is too short",
                maxlength:"Firstname is too long"
            },
            last_name:{
               	required:"Please Enter Lastname",
               	minlength:"Lastname is too short",
               	maxlength:"Lastname is too long"
            },
            email:{
            	required:"Please Enter your email address.",
            	email:"Invalid Email address.",
            	remote:"Email is already registered."
            },
            phone:{
            	required:"Please enter phone number",
            	minlength:"Invalid phone number.",
            	maxlength:"Phone number is too long.",
            	remote: "Phone number is already registered."
            }
		}	
	});
</script>