@extends('layouts.email')
@section('content-email')

<tr>
		            <td class="bg_white email-section">
		            	<div class="heading-section" style="text-align: center; padding: 0 30px;">
		              	<h2 align="left">Hello!</h2>
		               </div>
		            	<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
		            		<tr>
		            			<td valign="top" width="100%" style="padding-top: 20px;" class="services">
                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                            
                          <tr>
                            <td width="100%" class="text-services"><h3 align="left">You are receiving this email because we received a password reset request for your account.</h3></td>
                          </tr>
                          <tr>
                            <td width="100%" class="text-services"><p><a href="{{$link}}" class="btn btn-white-outline">Reset Password</a></p></td>
                          </tr>
                          <tr>
                            <td width="100%" class="text-services"><h3 align="left">This password reset link will expire in 60 minutes.<br>If you did not request a password reset, no further action is required.</h3></td>
                          </tr>
                          <tr> 
                            <td width="100%" class="text-services"><h3 align="left">Thanks <br> Order Management System</h3></td>
                          </tr>
                          
                        </table>
                      </td>
                      
                      
                    </tr><!-- end: tr -->
		            	</table>
		            </td>
		          </tr><!-- end: tr -->



@endsection