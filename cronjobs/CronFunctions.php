<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/cronjobs/CronManagers.php";

class CronFunctions{
    public static $user_os = "";
    public static $user_browser = "";
    public static $user_ip = "";
    public static $email = "";
    public static $whom = null;
    public static $gid = "";
    public static $name = "";
    public static $surname = "";
    public static $fullname = "";
    public static $modulesStr = "";
    public static $functionsStr = "";
    public static $userGroupId = 0;
    public static $userOrgCode = "";
    public static $registryNo = "";

    public static function cURL_GET($dataArray, $urL, $ssl=FALSE, $followLocation=TRUE, $returnTransfer=TRUE, $timeOut = null, $headers = [], $file=null){
        $ch = curl_init();
        if ($dataArray != null) {
            $data = http_build_query($dataArray);
            $getUrl = $urL . "?" . $data;
        } else {
            $getUrl = $urL;
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $ssl);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $followLocation);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, $returnTransfer);
		if($timeOut) curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		if(!empty($headers)) curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		if($file) curl_setopt($ch, CURLOPT_FILE, $file);
        curl_setopt($ch, CURLOPT_URL, $getUrl);
        $response = curl_exec($ch);

        if (curl_error($ch)) {
            echo 'Request Error:' . curl_error($ch);
        }
        curl_close($ch);
        return $response;
    }

    static function getUserIP(){
        $ip = $_SERVER["HTTP_CLIENT_IP"] ??
            $_SERVER["HTTP_X_FORWARDED_FOR"] ??
            $_SERVER["HTTP_X_FORWARDED"] ??
            $_SERVER["HTTP_FORWARDED_FOR"] ??
            $_SERVER["HTTP_FORWARDED"] ??
            $_SERVER["REMOTE_ADDR"];
        if (strpos($ip, ',') > 0)
            $ip = substr($ip, 0, strpos($ip, ','));
        return $ip;
    }

    public static function getOneXMailContent($bodyContent = "", $headerContent = ""): string{
        return '<html><head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
             </head>
             <body style="padding: 0; margin: 0; font-size: 12px; line-height: 16px; background: #E1E1D7; height: 100%; width: 100%; mso-line-height-rule: exactly; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; font-family: Arial, Helvetica, sans-serif;" bgcolor="#E1E1D7" text="#000000" marginheight="0" marginwidth="0" leftmargin="0" topmargin="0">
              <div style="display: none; font-size: 1px; color: #E1E1D7; line-height: 1px; max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden; mso-line-height-rule: exactly; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;"></div>
              <a name="oben" style="text-decoration: none; margin: 0px; padding: 0px; mso-line-height-rule: exactly; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;"></a>
              <!--[if gte mso 15]><div><p style="margin:0; font-size:0; line-height:0;"><o:p xmlns:o="urn:schemas-microsoft-com:office:office">&nbsp;</o:p></p><table cellspacing="0" cellpadding="0" width="100%" style="width:100%; background:#E1E1D7; background-color:#E1E1D7" bgcolor="#E1E1D7"><tr><td align="center"><table cellspacing="0" cellpadding="0" width="600" style="width:600px; background:#ffffff" bgcolor="#ffffff"><tr><td><![endif]-->
              <div style="mso-line-height-rule: exactly; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;">
               <p style="margin-top: 0; margin-right: 0; margin-left: 0; margin-bottom: 0; padding: 0; font-family: Arial, Helvetica, sans-serif; margin: 0; font-size: 0; line-height: 0; mso-line-height-rule: exactly; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;">
                <o:p xmlns:o="urn:schemas-microsoft-com:office:office">
                 &nbsp;
                </o:p></p>
               <table cellspacing="0" cellpadding="0" width="100%" style="width: 100%; background: #E1E1D7; background-color: #E1E1D7; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; mso-line-height-rule: exactly; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;" bgcolor="#E1E1D7">
                <tbody>
                 <tr> 
                  <td align="center" style="margin: 0; padding: 0; font-size: 0; line-height: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; mso-line-height-rule: exactly; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;">
                   <table cellspacing="0" cellpadding="0" width="600" style="width: 600px; background: #ffffff; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; mso-line-height-rule: exactly; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;" bgcolor="#ffffff">
                    <tbody>
                     <tr style="page-break-before: always;"> 
                      <td style="margin: 0; padding: 0; font-size: 0; line-height: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; mso-line-height-rule: exactly; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;"><img width="600" alt="line" height="1" src="https://scd.siemens.com/img/nlg/newton/image001.png" style="-ms-interpolation-mode: bicubic; border: 0; line-height: 100%; outline: none; text-decoration: none;"></td>
                     </tr>
                    </tbody>
                   </table> </td>
                 </tr>
                </tbody>
               </table>
              </div>
              <!--START-->
              <div class="sim-row first-sim-row ui-draggable" style="-ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly;" data-id="1"> 
               <!--LOGO SIEMENS--> 
               <p style="margin: 0px; padding: 0px; line-height: 0; font-family: Arial, Helvetica, sans-serif; font-size: 0px; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly;"> 
                <o:p xmlns:o="urn:schemas-microsoft-com:office:office">
                 &nbsp;
                </o:p> </p> 
               <table width="100%" style="background: rgb(225, 225, 215); width: 100%; border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;" bgcolor="#e1e1d7" cellspacing="0" cellpadding="0"> 
                <tbody>
                 <tr> 
                  <td align="center" style="margin: 0px; padding: 0px; line-height: 0; font-size: 0px; border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;"> 
                   <table width="600" style="background: rgb(255, 255, 255); width: 600px; border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;" bgcolor="#ffffff" cellspacing="0" cellpadding="0"> 
                    <tbody>
                     <tr> 
                      <td style="margin: 0px; padding: 0px; line-height: 0; font-size: 0px; border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;">
            
            </td> 
                     </tr> 
                     <tr> 
                      <td align="left" valign="bottom" style="margin: 0px; padding: 7px 40px 2px 30px; line-height: 0; font-size: 0px; border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;"> 
                       <table width="225" style="width: 225px; border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;margin:auto !important;" cellspacing="0" cellpadding="0"> 
                        <tbody>
                         <tr> 
                          <td style="margin: 0px; padding: 0px 0px 6px; line-height: 0; font-size: 0px; border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;"> 
                           <table width="225" style="width: 225px; border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;margin:auto !important;" cellspacing="0" cellpadding="0"> 
                            <tbody>
                             <tr> 
                              <td style="margin: 0px; padding: 0px; line-height: 0; font-size: 0px; border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;">
                               <div style="-ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly;"> 
                                    <img src="data:image/png;base64,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"/>
                               </div></td>
                    
                             </tr> 
                            </tbody>
                           </table> </td> 
                         </tr> 
                        </tbody>
                       </table> </td> 
                     </tr> 
                    </tbody>
                   </table> </td> 
                 </tr> 
                </tbody>
               </table> 
              </div>
              <div class="sim-row ui-draggable" style="-ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly;" data-id="6"> 
               <!--HEADER --> 
               <p style="margin: 0px; padding: 0px; line-height: 0; font-family: Arial, Helvetica, sans-serif; font-size: 0px; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly;"> 
                <o:p xmlns:o="urn:schemas-microsoft-com:office:office">
                 &nbsp;
                </o:p> </p> 
               <table width="100%" style="background: rgb(225, 225, 215); width: 100%; border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;" bgcolor="#e1e1d7" cellspacing="0" cellpadding="0"> 
                <tbody>
                 <tr> 
                  <td align="center" style="margin: 0px; padding: 0px; line-height: 0; font-size: 0px; border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;"> 
                   <table width="600" style="background: rgb(255, 255, 255); width: 600px; border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;" bgcolor="#ffffff" cellspacing="0" cellpadding="0"> 
                    <tbody>
                     <tr style="page-break-before: always;"> 
                      <td style="margin: 0px; padding: 0px; line-height: 0; font-size: 0px; border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;"><img width="600" height="1" style="border: 0px currentColor; border-image: none; line-height: 100%; text-decoration: none; -ms-interpolation-mode: bicubic;" alt="" src="https://scd.siemens.com/img/nlg/newton/image001.png" data-src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAlgAAAABCAYAAAACeNDKAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAAfSURBVEhLY3j87OUoHsWjeBSP4lE8ikfxKKYafskAAJodUYtnbHOlAAAAAElFTkSuQmCCAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA==" data-width="600" data-height="1"></td> 
                     </tr> 
                     <tr> 
                      <td align="left" style="background: rgb(235, 240, 245); margin: 0px; padding: 12px 40px 12px 30px; line-height: 0; font-size: 0px; border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;" bgcolor="#ebf0f5"> 
                       <table width="100%" style="width: 100%; border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;" cellspacing="0" cellpadding="0"> 
                        <tbody>
                         <tr> 
                          <td valign="top" style="margin: 0px; padding: 0px; line-height: 0; font-size: 0px; border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;"> 
                           <table width="100%" style="width: 100%; border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;" cellspacing="0" cellpadding="0"> 
                            <tbody>
                             <tr> 
                              <td class="sim-row-edit" style="margin: 0px; padding: 0px; line-height: 20px; font-family: Arial, Helvetica, sans-serif; font-size: 14px; border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;" data-type="title"> <span style="margin: 0px; padding: 0px; font-family: Microsoft JhengHei; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly;"><span style="margin: 0px; padding: 0px; color: rgb(120, 135, 145); line-height: 20px; font-family: Arial, Helvetica, sans-serif; font-size: 14px; display: block; -ms-word-wrap: break-word; min-width: 530px; max-width: 530px; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; overflow-wrap: break-word; mso-line-height-rule: exactly; -webkit-line-break: after-white-space;">
                              Digital Transformation creates value for business
                              </span></span> </td> 
                             </tr> 
                            </tbody>
                           </table> </td> 
                         </tr> 
                        </tbody>
                       </table> </td> 
                     </tr> 
                     <tr> 
                      <td style="margin: 0px; padding: 0px; line-height: 0; font-size: 0px; border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;"> 
                       <table width="100%" align="left" style="width: 100%; border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;" cellspacing="0" cellpadding="0"> 
                        <tbody>
                         <tr> 
                          <td align="left" valign="top" style="background: linear-gradient(to right, rgb(80, 190, 190) 0%, rgb(0, 153, 153) 50%, rgb(0, 153, 176) 83%, rgb(0, 153, 203) 100%); margin: 0px; padding: 0px; line-height: 0; font-size: 0px; border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;" bgcolor="#50bebe"> 
                           <table width="100%" style="width: 100%; border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;" cellspacing="0" cellpadding="0"> 
                            <tbody>
                             <tr> 
                              <td background="https://scd.siemens.com/img/nlg/gradient.png" valign="top" style="margin: 0px; padding: 0px; line-height: 0; font-size: 0px; border-collapse: collapse; -ms-text-size-adjust: 100%; background-image: url(&quot;https://scd.siemens.com/img/nlg/gradient.png&quot;); background-size: 100% 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;"> 
                            
                               <div style="-ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly;"> 
                                <div style="line-height: 0px; font-size: 0px; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly;"> 
                                 <table width="100%" style="width: 100%; border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;" cellspacing="0" cellpadding="0"> 
                                  <tbody>
                                   <tr> 
                                    <td style="margin: 0px; padding: 0px 0px 28px; line-height: 0; font-size: 0px; border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;"> 
                                     <table width="100%" style="width: 100%; border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;" cellspacing="0" cellpadding="0"> 
                                      <tbody>
                                       <tr> 
                                        <td valign="top" style="margin: 0px; padding: 22px 40px 9px 0px; line-height: 0; font-size: 0px; border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;"> 
                                         <table width="100%" style="width: 100%; border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;" cellspacing="0" cellpadding="0"> 
                                          <tbody>
                                           <tr> 
                                            <td class="sim-row-edit" style="margin: 0px; padding: 0px 0px 0px 30px; line-height: 44px; font-family: Arial, Helvetica, sans-serif; font-size: 36px; border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;" data-type="title"> <span style="margin: 0px; padding: 0px; font-family: Microsoft JhengHei; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly;"><span style="margin: 0px; padding: 0px; color: rgb(255, 255, 255); line-height: 44px; font-family: Arial, Helvetica, sans-serif; font-size: 36px; display: block; -ms-word-wrap: break-word; min-width: 530px; max-width: 530px; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; overflow-wrap: break-word; mso-line-height-rule: exactly; -webkit-line-break: after-white-space; mso-text-raise: 3px;">
                                                
                                                ' . $headerContent . '
                                                
                                                </span></span> </td> 
                                           </tr> 
                                          </tbody>
                                         </table> </td> 
                                       </tr> 
                                       <tr> 
                                        <td valign="top" style="margin: 0px; padding: 0px 40px 9px 0px; line-height: 0; font-size: 0px; border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;"> 
                                         <table width="100%" style="width: 100%; border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;" cellspacing="0" cellpadding="0"> 
                                          <tbody>
                                           <tr> 
                                            <td class="sim-row-edit" style="margin: 0px; padding: 0px 0px 0px 30px; line-height: 26px; font-family: Arial, Helvetica, sans-serif; font-size: 18px; border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;" data-type="title"> <span style="margin: 0px; padding: 0px; font-family: Microsoft JhengHei; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly;"><span style="margin: 0px; padding: 0px; color: rgb(255, 255, 255); line-height: 26px; font-family: Arial, Helvetica, sans-serif; font-size: 18px; display: block; -ms-word-wrap: break-word; min-width: 530px; max-width: 530px; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; overflow-wrap: break-word; mso-line-height-rule: exactly; -webkit-line-break: after-white-space; mso-text-raise: 3px;">SI EA O AIS THA</span></span> </td> 
                                           </tr> 
                                          </tbody>
                                         </table> </td> 
                                       </tr> 
                                      </tbody>
                                     </table> </td> 
                                   </tr> 
                                  </tbody>
                                 </table> 
                                </div> 
                               </div>
                                </td> 
                             </tr> 
                            </tbody>
                           </table> </td> 
                         </tr> 
                        </tbody>
                       </table> </td> 
                     </tr> 
                    </tbody>
                   </table> </td> 
                 </tr> 
                </tbody>
               </table> 
              </div>
              <div class="sim-row ui-draggable" style="-ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly;" data-id="12"> 
               <!-- Text --> 
               <p style="margin: 0px; padding: 0px; line-height: 0; font-family: Arial, Helvetica, sans-serif; font-size: 0px; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly;"> 
                <o:p xmlns:o="urn:schemas-microsoft-com:office:office">
                 &nbsp;
                </o:p> </p> 
               <table width="100%" style="background: rgb(225, 225, 215); width: 100%; border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;" bgcolor="#e1e1d7" cellspacing="0" cellpadding="0"> 
                <tbody>
                 <tr> 
                  <td align="center" style="margin: 0px; padding: 0px; line-height: 0; font-size: 0px; border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;"> 
                   <table width="600" style="background: rgb(255, 255, 255); width: 600px; border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;" bgcolor="#ffffff" cellspacing="0" cellpadding="0"> 
                    <tbody>
                     <tr style="page-break-before: always;"> 
                      <td style="margin: 0px; padding: 0px; line-height: 0; font-size: 0px; border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;"><img width="600" height="1" style="border: 0px currentColor; border-image: none; line-height: 100%; text-decoration: none; -ms-interpolation-mode: bicubic;" alt="" src="https://scd.siemens.com/img/nlg/newton/image001.png" data-src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAlgAAAABCAYAAAACeNDKAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAAfSURBVEhLY3j87OUoHsWjeBSP4lE8ikfxKKYafskAAJodUYtnbHOlAAAAAElFTkSuQmCCAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA==" data-width="600" data-height="1"></td> 
                     </tr> 
                     <tr> 
                      <td align="left" style="margin: 0px; padding: 24px 40px 10px 30px; line-height: 0; font-size: 0px; border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;"> 
                       <table width="100%" style="width: 100%; border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;" cellspacing="0" cellpadding="0"> 
                        <tbody>
                         <tr> 
                          <td valign="top" style="margin: 0px; padding: 0px 0px 10px; line-height: 0; font-size: 0px; border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;"> 
                           <table width="100%" style="width: 100%; border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;" cellspacing="0" cellpadding="0"> 
                            <tbody>
                             <tr> 
                              <td class="sim-row-edit" style="margin: 0px; padding: 0px; line-height: 20px; font-family: Arial, Helvetica, sans-serif; font-size: 14px; border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;" data-type="text2"> <span style="margin: 0px; padding: 0px; font-family: Microsoft JhengHei; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly;"><span class="sim-row-edit-span" style="margin: 0px; padding: 0px; color: rgb(45, 55, 60); line-height: 20px; font-family: Arial, Helvetica, sans-serif; font-size: 14px; display: block; -ms-word-wrap: break-word; min-width: 530px; max-width: 530px; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; overflow-wrap: break-word; mso-line-height-rule: exactly; -webkit-line-break: after-white-space;">
                                    ' . $bodyContent . '
                                    <br><br>
                                    <br>
                                    This automated email is sent by <b>OneX</b><br>
                                    <b>SI EA O AIS THA ME</b>
                              </td> 
                             </tr> 
                            </tbody>
                           </table> </td> 
                         </tr> 
                        </tbody>
                       </table> </td> 
                     </tr> 
                    </tbody>
                   </table> </td> 
                 </tr> 
                </tbody>
               </table> 
              </div>
            
              <div class="sim-row ui-draggable" style="color: rgb(173, 190, 203); text-decoration: none; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly;" data-id="20"> 
               <!-- Footer dark --> 
               <p style="margin: 0px; padding: 0px; line-height: 0; font-family: Arial, Helvetica, sans-serif; font-size: 0px; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly;"> 
                <o:p xmlns:o="urn:schemas-microsoft-com:office:office">
                 &nbsp;
                </o:p> </p> 
               <table width="100%" style="background: rgb(225, 225, 215); width: 100%; border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;" bgcolor="#e1e1d7" cellspacing="0" cellpadding="0"> 
                <tbody>
                 <tr> 
                  <td align="center" style="margin: 0px; padding: 0px; color: rgb(173, 190, 203); line-height: 0; font-size: 0px; text-decoration: none; border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;"> 
                   <table width="600" style="background: rgb(60, 70, 75); width: 600px; border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;" bgcolor="#3c464b" cellspacing="0" cellpadding="0"> 
                    <tbody>
                     <tr style="page-break-before: always;"> 
                      <td style="margin: 0px; padding: 0px; color: rgb(173, 190, 203); line-height: 0; font-size: 0px; text-decoration: none; border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;"><img width="600" height="1" style="border: 0px currentColor; border-image: none; line-height: 100%; text-decoration: none; -ms-interpolation-mode: bicubic;" alt="" src="https://scd.siemens.com/img/nlg/newton/image001.png" data-src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAlgAAAABCAYAAAACeNDKAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAAfSURBVEhLY3j87OUoHsWjeBSP4lE8ikfxKKYafskAAJodUYtnbHOlAAAAAElFTkSuQmCCAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA==" data-width="600" data-height="1"></td> 
                     </tr> 
                     <tr> 
                      <td align="left" style="margin: 0px; padding: 29px 80px 0px 30px; color: rgb(173, 190, 203); line-height: 0; font-size: 0px; text-decoration: none; border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;"> 
                       <table width="100%" style="width: 100%; border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;" cellspacing="0" cellpadding="0"> 
                        <tbody>
                         <tr> 
                          <td valign="top" style="margin: 0px; padding: 0px 0px 11px; color: rgb(173, 190, 203); line-height: 0; font-size: 0px; text-decoration: none; border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;"> 
                           <div style="-ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly;"> 
                            <!-- Links / Linkslist / Single link --> 
                            <table width="100%" style="width: 100%; border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;" cellspacing="0" cellpadding="0"> 
                             <tbody>
                              <tr> 
                               <td valign="top" style="margin: 0px; padding: 0px 0px 4px; color: rgb(173, 190, 203); line-height: 0; font-size: 0px; text-decoration: none; border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;"> 
                                <table width="100%" style="width: 100%; border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;" cellspacing="0" cellpadding="0"> 
                                 <tbody>
                                  <tr> 
                                  </tr> 
                                 </tbody>
                                </table> </td> 
                              </tr> 
                             </tbody>
                            </table> 
                           </div> </td> 
                         </tr> 
                         <tr> 
                          <td valign="top" style="margin: 0px; padding: 0px 0px 20px; color: rgb(173, 190, 203); line-height: 0; font-size: 0px; text-decoration: none; border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;"> 
                           <table align="left" style="border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;" cellspacing="0" cellpadding="0"> 
                            <tbody>
                             <tr> 
                              <td style="margin: 0px; padding: 0px 12px 0px 0px; color: rgb(173, 190, 203); line-height: 0; font-size: 0px; text-decoration: none; border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;"> 
                               <table align="left" style="border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;" cellspacing="0" cellpadding="0"> 
                                <tbody>
                                 <tr> 
                                  <td style="margin: 0px; padding: 0px; color: rgb(173, 190, 203); line-height: 16px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; text-decoration: none; border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;"> <span style="margin: 0px; padding: 0px; font-family: Microsoft JhengHei; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly;"><span style="margin: 0px; padding: 0px; color: rgb(173, 190, 203); line-height: 16px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; display: block; -ms-word-wrap: break-word; min-width: 450px; max-width: 450px; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; overflow-wrap: break-word; mso-line-height-rule: exactly; -webkit-line-break: after-white-space;"> <a style="color: rgb(255, 255, 255); text-decoration: none; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly;" href="https://onex.siemens.com.tr"><span style="margin: 0px; padding: 0px; color: rgb(255, 255, 255); font-family: Arial, Helvetica, sans-serif; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly;"><font color="#ffffff">OneX Portal</font></span></a> </span></span> </td> 
                                 </tr> 
                                </tbody>
                               </table> </td> 
                             </tr> 
                            </tbody>
                           </table> 
                           <table align="left" style="border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;" cellspacing="0" cellpadding="0"> 
                            <tbody>
                             <tr> 
                              <td style="margin: 0px; padding: 0px; color: rgb(173, 190, 203); line-height: 0; font-size: 0px; text-decoration: none; border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;"> 
                               <table align="left" style="border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;" cellspacing="0" cellpadding="0"> 
                                <tbody>
                                 <tr> 
                                  <td style="margin: 0px; padding: 0px; color: rgb(173, 190, 203); line-height: 16px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; text-decoration: none; border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;"> <span style="margin: 0px; padding: 0px; font-family: Microsoft JhengHei; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly;"><span style="margin: 0px; padding: 0px; color: rgb(173, 190, 203); line-height: 16px; font-family: Arial, Helvetica, sans-serif; font-size: 12px; display: block; -ms-word-wrap: break-word; min-width: 450px; max-width: 450px; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; overflow-wrap: break-word; mso-line-height-rule: exactly; -webkit-line-break: after-white-space;"> Restricted © Siemens ' . date("Y") . ' </span></span> </td> 
                                 </tr> 
                                </tbody>
                               </table> </td> 
                             </tr> 
                            </tbody>
                           </table> </td> 
                         </tr> 
                         <tr> 
                          <td valign="top" style="margin: 0px; padding: 9px 0px 20px; color: rgb(173, 190, 203); line-height: 0; font-size: 0px; text-decoration: none; border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;"> 
                           <table width="100%" style="width: 100%; border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;" cellspacing="0" cellpadding="0"> 
                            <tbody>
                             <tr> 
                              <td class="sim-row-edit" style="margin: 0px; padding: 0px; color: rgb(173, 190, 203); line-height: 15px; font-family: Arial, Helvetica, sans-serif; font-size: 11px; text-decoration: none; border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly; mso-table-lspace: 0pt; mso-table-rspace: 0pt;" data-type="title"> <span style="margin: 0px; padding: 0px; font-family: Microsoft JhengHei; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly;"><span style="margin: 0px; padding: 0px; color: rgb(173, 190, 203); line-height: 15px; font-family: Arial, Helvetica, sans-serif; font-size: 11px; display: block; -ms-word-wrap: break-word; min-width: 450px; max-width: 450px; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; overflow-wrap: break-word; mso-line-height-rule: exactly; -webkit-line-break: after-white-space;"> &nbsp; </span></span> </td> 
                             </tr> 
                            </tbody>
                           </table> </td> 
                         </tr> 
                        </tbody>
                       </table> </td> 
                     </tr> 
                    </tbody>
                   </table> </td> 
                 </tr> 
                </tbody>
               </table> 
              </div>
              <!--END-->
              <div style="mso-line-height-rule: exactly; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;">
               <p style="margin-top: 0; margin-right: 0; margin-left: 0; margin-bottom: 0; padding: 0; font-family: Arial, Helvetica, sans-serif; margin: 0; font-size: 0; line-height: 0; mso-line-height-rule: exactly; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;">
                <o:p xmlns:o="urn:schemas-microsoft-com:office:office">
                 &nbsp;
                </o:p></p>
               <table cellspacing="0" cellpadding="0" width="100%" style="width: 100%; background: #E1E1D7; background-color: #E1E1D7; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; mso-line-height-rule: exactly; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;" bgcolor="#E1E1D7">
                <tbody>
                 <tr> 
                  <td align="center" style="margin: 0; padding: 0; font-size: 0; line-height: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; mso-line-height-rule: exactly; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;">
                   <table cellspacing="0" cellpadding="0" width="600" style="width: 600px; background: #ffffff; background-color: #ffffff; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; mso-line-height-rule: exactly; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;" bgcolor="#ffffff">
                    <tbody>
                     <tr style="page-break-before: always;"> 
                      <td style="margin: 0; padding: 0; font-size: 0; line-height: 0; background: #3C464B; background-color: #3C464B; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; mso-line-height-rule: exactly; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;" bgcolor="#3C464B"><img width="600" alt="line" height="1" src="https://scd.siemens.com/img/nlg/newton/image001.png" style="-ms-interpolation-mode: bicubic; border: 0; line-height: 100%; outline: none; text-decoration: none;"></td>
                     </tr>
                    </tbody>
                   </table> </td>
                 </tr>
                </tbody>
               </table>
              </div>
              <!--[if gte mso 15]></td></tr></table>	</td></tr></table></div><![endif]-->
             </body>
            </html>
        ';
    }

}