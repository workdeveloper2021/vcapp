<!DOCTYPE html>
<html lang="en">

<head>
    <title>e-Receipt</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
    </style>

</head>

<body style="background: #FFFFFF;">


    <table cellpadding="0" cellspacing="0" width="100%" style="font-family: Arial, Helvetica, sans-serif;font-size:14px;border:0;max-width: 640px;border-top:4px solid #1d99d5;height:auto;min-height:200px;background: #FFFFFF;margin: auto;color: #363636;">
        <tbody style="width: 100%;">
            <tr class="heightspacer">
                <td height="40"></td>
            </tr>
            <tr style="height: auto;">
                <td style="padding:0px;">
                    <table cellpadding="0" cellspacing="0" width="100%">
                        <tbody style="width: 100%;">
                            <tr class="heightspacer">
                                <td cellpadding="0" colspan="3" height="20" style = "text-align: center"><strong>Booking Status: <span style="color: 2b9354"><?=$payment_status;?></span></strong></td>
                            </tr>
                            <tr style="height: auto;">
                                <td class="widthspacer" width="20"></td>
                                <td>
                                    <table cellpadding="0" cellspacing="0" width="100%">
                                        <tbody>
                                            <tr style="height: auto;">
                                                <td style="text-align:left;padding: 0px;vertical-align: middle;">
                                                   <h4 style="margin: 0px;font-weight:bold;font-size:16px;"> <?=$business_name;?> </h4>
                                                   <p class="heightspacer" style="height: 5px;"></p>
                                                   <p style="margin: 0px;"> <?=$address;?> </p>
                                                   <p class="heightspacer" style="height: 5px;"></p>
                                                   <p style="margin: 0px;"> <?=$business_phone;?> </p>
                                                   <p class="heightspacer" style="height: 5px;"></p>
                                                   <p style="margin: 0px;"> <?=$business_email;?> </p>
                                                </td>
                                                <td style="text-align:right;padding: 0px;vertical-align: top;">
                                                    <img src="<?=$business_image;?>" style="max-width: 50%;height: auto; border-radius: 15px;">
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                                <td class="widthspacer" width="20"></td>
                            </tr>
                            <tr class="heightspacer">
                                <td colspan="3" height="20"></td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr class="heightspacer">
                <td height="10"></td>
            </tr>
            <tr style="height: auto;">
                <td style="padding:0px;">
                    <table cellpadding="0" cellspacing="0" width="100%">
                        <tbody style="width: 100%;">
                            <tr class="heightspacer">
                                <td cellpadding="0" colspan="3" height="20"></td>
                            </tr>
                            <tr style="height: auto;">
                                <td class="widthspacer" width="20"></td>
                                <td>
                                    <table cellpadding="0" cellspacing="0" width="100%">
                                        <tbody>
                                            <tr style="height: auto;">
                                                <td style="text-align:left;padding: 0px;vertical-align: top;">
                                                    <p style="margin: 0px;"><span style="margin: 0px;font-weight:bold;font-size:16px;">Service Provider Name:</span> <?=$instructor_name;?> </p>
                                                   <?php
                                                    if (!empty($registration)) {
                                                        echo '<p class="heightspacer" style="height: 5px;"></p>';
                                                        echo '<p style="margin: 0px;"><span style="margin: 0px;font-weight:bold;font-size:16px;">Registration Number:</span> '.$registration.' </p>';
                                                    }
                                                   ?>
                                                   <p class="heightspacer" style="height: 5px;"></p>
                                                   <p style="margin: 0px;"> <?=$instructor_address;?> </p>
                                                   
                                                </td>
                                                <td style="text-align:right;padding: 0px;vertical-align: top;">
                                                    <p><?=$shift_date;?></p>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                                <td class="widthspacer" width="20"></td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr class="heightspacer">
                <td height="15"></td>
            </tr>
            <tr style="height: auto;">
                <td style="padding:0px;">
                    <table cellpadding="0" cellspacing="0" width="100%" style="border:1px solid #2b9354;">
                        <thead bgcolor="#2b9354">
                            <tr class="heightspacer">
                                <th style="width: 100%;" cellspacing="0" width="100%" colspan="4" height="15"></th>
                            </tr>
                            <tr>
                                <th class="widthspacer" width="10"></th>
                                <th>
                                   <h4 style="color:#FFFFFF;font-size: 15px;text-align:left;"> Receipt </h4> <!-- Duration -->
                                </th>
                                <th>
                                    <h4 style="color:#FFFFFF;font-size: 15px;text-align:right;">#<?=$transaction_id;?> </h4> <!-- $duration Days -->
                                </th>
                                <th class="widthspacer" width="10"></th>
                            </tr>
                            <tr class="heightspacer">
                                <th colspan="4" height="15"></th>
                            </tr>
                        </thead>
                        <tbody style="width: 100%;">
                            <tr class="heightspacer">
                                <td cellpadding="0" colspan="4" height="20"></td>
                            </tr>
                            <tr class="heightspacer">
                                <td class="widthspacer" width="10"></td>
                                <td style="text-align:left;padding: 0px;vertical-align: top;">
                                   <h4 style="margin: 0px;font-weight:normal;"> <span style="font-size:16px;font-weight:bold;">Client Name:</span> <?=$customer_name;?> </h4>
                                   <p class="heightspacer" style="height: 10px;"></p>
                                   <p style="margin: 0px; font-size: 14px;"> <?=$customer_address;?></p>
                                   <h4 style="margin: 10px 0px 0px 0px;font-weight:normal;"> <span style="font-size:16px;font-weight:bold;">Service Name:</span> <?=$service_type;?> </h4>
                                   <p class="heightspacer" style="height: 10px;"></p>
                                   <p style="margin: 0px;"> <span style="font-size:16px;font-weight:bold;">Duration:</span> <?=$duration;?>m 
                                   <?php /*
                                   <a href="<?=$map_url;?>" target="_blank"><?=$location_name;?></a> </p> */ ?>
                                </td>
                                <td style="text-align:right;padding: 0px;vertical-align: top;">
                                    <p style="margin: 0px;font-weight:bold;"> Subtotal $<?=$amount;?> </p>
                                    <p class="heightspacer" style="height: 10px;"></p>
                                    <p style="margin: 0px;font-weight:bold;"> Tax1: $<?=$tax1;?></p>
									<p class="heightspacer" style="height: 10px;"></p>
                                    <p style="margin: 0px;font-weight:bold;"> Tax2: $<?=$tax2;?></p>
                                    <p class="heightspacer" style="height: 10px;"></p>
                                    <p style="margin: 0px;font-weight:bold;"> Total $<?=$grand;?> </p>
                                    <p class="heightspacer" style="height: 10px;"></p>
                                    <p style="margin: 0px;font-weight:bold;"> Outstanding $0.00 </p>
                                </td>
                                <td class="widthspacer" width="10"></td>
                            </tr>
                            <tr class="heightspacer">
                                <td cellpadding="0" colspan="4" height="30"></td>
                            </tr>
                            <tr class="heightspacer">
                                <td cellpadding="0" colspan="4" height="30" style="border-top: 1px solid #ddd;"></td>
                            </tr>
                            <tr class="heightspacer">
                                <td class="widthspacer" width="10"></td>
                                <td style="text-align:left;padding: 0px;vertical-align: middle;">
                                   <h4 style="margin: 0px;font-weight:normal;font-size:18px;"> Amount for the service </h4>
                                </td>
                                <td style="text-align:right;padding: 0px;vertical-align: middle;">
                                   <h4 style="margin: 0px;font-weight:normal;font-size:18px;font-weight: bold;"> $<?=$grand;?> </h4>
                                </td>
                                <td class="widthspacer" width="10"></td>
                            </tr>
                            <tr class="heightspacer">
                                <td cellpadding="0" colspan="4" height="30"></td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>

</body>

</html>