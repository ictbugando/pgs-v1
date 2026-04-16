<section class="vh-100" style="background-image:url('<?php echo base_url('images/bg-image.jpg'); ?>');background-size: 100% 100%; background-repeat: no-repeat;">
   <div class="container h-100 pt-1 pb-3" style="overflow:scroll;height:auto;scrollbar-width: none;">
      <div class="row d-flex justify-content-center align-items-center h-auto" style="padding:0px;margin:0px;">
         <div class="col col-xl-10" style="padding:0px;margin:0px;">
            <div class="card bg-transparent" style="border-width: 0" >
               <div class="row g-0">
                  <div class="col-md-6 col-lg-5 d-none d-md-block" >
                     <h2 style="color:#000;font-weight:bold;text-align:center;margin:20px auto;">BUGANDO PGS</h2>
                     <hr />                  
                     <img src="<?php echo base_url('images/wallets.png'); ?>"
                        alt="login form" style=" width:380px; height:242px; " />
                     <div style="margin:0px auto;width:70%;position:absolute;bottom:0;">
                        <img src="<?php echo base_url('images/lipa-switch-nw.png'); ?>" height="70px" style="margin-top:20px;display:; visibility:hidden;" />
                     </div>
                  </div>
                  <div class="col-md-6 col-lg-7 d-flex background:blue;">
                     <div class="card-body p-4  text-black" style="padding:0px;margin:0px;">
                        <div style=" margin:0px auto; width:35%;">
                           <img src="<?php echo base_url('images/bugando-transparent.png'); ?>"
                              alt="logo" class="img-fluid" width="160px" style="" />
                        </div>
                        <form method="post" action="<?php echo base_url('login/process'); ?>">
                           <h5 class="fw-normal mb-3 pb23" style="letter-spacing: 1px;margin-top:20px;"><?php echo $sesionLanguage[0]->loginHeaderLabel; ?></h5>
                           <div class="form-outline mb-2">
                              <input type="email" name="loginEmail" id="form2Example17" class="form-control form-control-lg" />
                              <label class="form-label" for="form2Example17"><?php echo $sesionLanguage[0]->loginEmailLabel; ?></label>
                           </div>
                           <div class="form-outline mb-2">
                              <input type="password" name="loginPassword" id="form2Example27" class="form-control form-control-lg" />
                              <label class="form-label" for="form2Example27"><?php echo $sesionLanguage[0]->loginPasswordLabel; ?></label>
                           </div>
                           <div class="pt-1 mb-2">
                              <div id="formPostErr" class="hideMe">
                                 <div  class="alert alert-warning alert-dismissible fade show formPostErr" role="alert">
                                    <strong><?php echo $sesionLanguage[0]->globalMessageString; ?>!</strong><br />
                                    <span id="alertMsg">&nbsp;</span>
                                 </div>
                              </div>
                              <div class="mySpinnerLogin">
                                 <div class="spinner-grow text-primary" role="status"></div>
                                 <div class="spinner-grow text-primary" role="status"></div>
                                 <div class="spinner-grow text-primary" role="status"></div>
                              </div>
                              <button style="width:100%;margin-top:15px;" class="btn btn-dark btn-lg btn-block login-btn" type="button"><?php echo $sesionLanguage[0]->loginButtonLable; ?></button>
                           </div>
                           <div class="form-group d-md-flex">
                              <!--
                              <div class="w-50 text-left">
                                 <label class="checkbox-wrap checkbox-primary mb-0"><?php echo $sesionLanguage[0]->loginRememberLabel; ?>
                                 <input type="checkbox" checked="">
                                 <span class="checkmark"></span>
                                 </label>
                              </div>
                              <div class="w-50 text-md-right">
                                 <a href="#"><?php echo $sesionLanguage[0]->loginForgotPassLabel; ?></a>
                              </div>
                           </div>
                           <a href="#!" class="small text-muted"><?php echo $sesionLanguage[0]->loginTermsLabel; ?></a>
                              -->
                        </form>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</section>
