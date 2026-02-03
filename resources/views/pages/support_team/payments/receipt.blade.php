<html>
<head>
    <title>Receipt_{{ $pr->ref_no.'_'.$sr->user->name }}</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/receipt.css') }}"/>
</head>
<body>
<div class="container">
    <div id="print" xmlns:margin-top="http://www.w3.org/1999/xhtml">
        {{--  School Details--}}
        <table width="100%">
            <tr>
                <td style="text-align: center;">
                    <strong><span style="color: #1b0c80; font-size: 25px;">
                        <img src="{{ asset('global_assets/images/logo.png') }}" alt="Eagles Logo" style="height: 30px; width: auto; vertical-align: middle; margin-right: 8px; opacity: 0.9;"/>
                        EAGLES COLLEGE
                    </span></strong><br/>
                    <strong><span style="color: #000; font-size: 15px;"><i>Excellence in Education Since 2022</i></span></strong><br/>
                    <strong><span style="color: #000; font-size: 15px;"><i>123 Education Road, Golden Acres City, kwekwe</i></span></strong><br/>
                    <strong><span style="color: #000; font-size: 15px;"><i>Phone: +234 801 234 5678 | Email: info@eaglescollege.edu.zw</i></span></strong>
                    <br/> <br/>
                    <span style="color: #000; font-weight: bold; font-size: 25px;"> PAYMENT RECEIPT</span>
                </td>
            </tr>
        </table>

        {{--Background Logo Watermark--}}
        <div style="position: fixed; text-align: center; z-index: -1; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-45deg); width: 400px; height: 400px; opacity: 0.1;">
            <img src="{{ asset('global_assets/images/logo.png') }}"
                 style="max-width: 100%; max-height: 100%;"/>
        </div>
        
        {{--Header Logo--}}
        <div style="text-align: center; margin-bottom: 20px;">
            <img src="{{ asset('global_assets/images/logo.png') }}" alt="Eagles Logo" style="height: 60px; width: auto; margin-bottom: 10px; opacity: 0.9;"/>
        </div>

        {{--Receipt No --}}
    <div class="bold arial" style="text-align: center; float:right; width: 200px; padding: 5px; margin-right:30px">
        <div style="padding: 10px 20px; width: 200px; background-color: lightcyan;">
            <span  style="font-size: 16px;">Receipt Reference No.</span>
        </div>
        <div  style="padding: 10px 20px; width: 200px; background-color: lightyellow;">
            <span  style="font-size: 25px;">{{ $pr->ref_no }}</span>
        </div>
    </div>

        <div style="clear: both"></div>

        {{-- Student Info --}}
        <div style="margin-top:5px; display: block; background-color: rgba(92, 172, 237, 0.12); padding: 5px; ">
            <span style="font-weight:bold; font-size: 20px; color: #000; padding-left: 10px">STUDENT INFORMATION</span>
        </div>

        {{--Photo--}}
        <div style="margin: 15px;">
            <img style="width: 100px; height: 100px; float: left;" src="{{ $sr->user->photo }}" alt="...">
        </div>

       <div style="float: left; margin-left: 20px">
           <table style="font-size: 16px" class="td-left" cellspacing="5" cellpadding="5">
               <tr>
                   <td class="bold">NAME:</td>
                   <td>{{ $sr->user->name }}</td>
               </tr>
               <tr>
                   <td class="bold">ADM_NO:</td>
                   <td>{{ $sr->adm_no }}</td>
               </tr>
               <tr>
                   <td class="bold">CLASS:</td>
                   <td>{{ $sr->my_class->name }}</td>
               </tr>
           </table>
       </div>
        <div class="clear"></div>

        {{-- Payment Info --}}
        <div style="margin-top:5px; display: block; background-color: rgba(92, 172, 237, 0.12); padding: 5px; ">
            <span style="font-weight:bold; font-size: 20px; color: #000; padding-left: 10px">PAYMENT INFORMATION</span>
        </div>

        <table class="td-left" style="font-size: 16px" cellspacing="2" cellpadding="2">
                <tr>
                    <td class="bold">REFERENCE:</td>
                    <td>{{ $payment->ref_no }}</td>
                    <td class="bold">TITLE:</td>
                    <td>{{ $payment->title }}</td>
                </tr>
                <tr>
                    <td class="bold">AMOUNT:</td>
                    <td>{{ $payment->amount }}</td>
                    <td class="bold">DESCRIPTION:</td>
                    <td>{{ $payment->description }}</td>
                </tr>
            </table>

        {{-- Payment Desc --}}
        <div style="margin-top:5px; display: block; background-color: rgba(92, 172, 237, 0.12); padding: 5px; ">
            <span style="font-weight:bold; font-size: 20px; color: #000; padding-left: 10px">DESCRIPTION</span>
        </div>

        <table class="td-left" style="font-size: 16px" width="100%" cellspacing="2" cellpadding="2">
           <thead>
           <tr>
               <td class="bold">Date</td>
               <td class="bold">Amount Paid <del style="text-decoration-style: double">$</del></td>
               <td class="bold">Balance <del style="text-decoration-style: double">$</del></td>
           </tr>
           </thead>
            <tbody>
            @foreach($receipts as $r)
                <tr>
                    <td>{{ date('D\, j F\, Y', strtotime($r->created_at)) }}</td>
                    <td>{{ $r->amt_paid }}</td>
                    <td>{{ $r->balance }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <hr>
        <div class="bold arial" style="text-align: center; float:right; width: 200px; padding: 5px; margin-right:30px">
            <div style="padding: 10px 20px; width: 200px; background-color: lightcyan;">
                <span  style="font-size: 16px;">{{ $pr->paid ? 'PAYMENT STATUS' : 'TOTAL DUE' }}</span>
            </div>
            <div  style="padding: 10px 20px; width: 200px; background-color: lightyellow;">
                <span  style="font-size: 25px;">{{ $pr->paid ? 'CLEARED' : $pr->balance }}</span>
            </div>
        </div>
        <div class="clear"></div>
    </div>
</div>
<script>
window.print();
</script>
</body>
</html>