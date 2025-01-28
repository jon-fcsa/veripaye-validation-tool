<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require("./vendor/autoload.php");
use Swaggest\JsonSchema\Schema;

$schemaJson = <<<'JSON'
{
    "$schema": "http://json-schema.org/draft-07/schema#",
    "type": "object",
    "properties": {
        "Ni": {"type": "string"},
        "PaymentDate": {"type": "number"},
        "Umbrella": {"type": "string"},
        "FileName": {"type": "string"},
        "ClientId": {"type": "string"},
        "Software": {"type": "string"},
        "TaxOffice": {"type": "string"},
        "RtiMatched": {"type": "boolean"},
        "DuplicateCheck": {"type": "boolean"},
        "File": {"type": "object"},
        "Period": {"type": "number"},
        "Errors": {
          "type": "array",
          "properties": {
              "CheckId": {"type": "string"},
              "Type": {"type": "number"},
              "Result": {"type": "string"},
              "Ni": {"type": "string"},
              "Investigate": {"type": "boolean"},
              "RunDate": {"type": "number"},
              "RunDateTime": {"type": "number"},
              "Area": {"type": "number"},
              "Umbrella": {"type": "string"},
              "Software": {"type": "string"},
              "Agency": {"type": "string"},
              "Warning": {"type": "string"},
              "FscaIgnore": {"type": "string"},
              "Expected": {"type": "string"},
              "Reported": {"type": "string"},
              "PaymentDate": {"type": "number"}
          }
        },
        "RunDate": {"type": "number"},
        "EmployeeDeductions": {
          "type": "object",
          "properties": {
              "NI": {"type": "number"},
              "IncomeTax": {"type": "number"},
              "StudentLoan": {"type": "number"},
              "EmployeePension": {"type": "number"},
              "PostGradLoan": {"type": "number"},
              "OtherPostTaxDeductions": {
                "type": "array",
                "properties": {
                    "Description": {"type": "string"},
                    "Amount": {"type": "number"}
                }
              },
              "OtherTotal": {"type": "number"}
          }
        },
        "EmployeePayment": {
          "type": "object",
          "properties": {
              "Rate": {"type": "number"},
              "Hours": {"type": "number"},
              "TotalBase": {"type": "number"},
              "Bonus": {"type": "number"},
              "HolidayPay": {"type": "number"},
              "Other": {"type": "array"},
              "OtherTotal": {"type": "number"},
              "Total": {"type": "number"}
          }
        },
        "PaymentPeriod": {
          "type": "object",
          "properties": {
              "PayDate": {"type": "string"},
              "TaxPeriod": {"type": "number"},
              "ActualTaxPeriod": {"type": "number"},
              "TaxYear": {"type": "string"},
              "TaxPeriodType": {"type": "string"},
              "PeriodsCovered": {"type": "number"}
          }
        },
        "HolidayPay": {
          "type": "object",
          "properties": {
              "Percentage": {"type": "number"},
              "AmountAdvanced": {"type": "number"},
              "AmountAccrued": {"type": "number"},
              "Method": {"type": "string"},
              "TotalAccrued": {"type": "number"},
              "TotalAccruedCosts": {"type": "number"}
          }
        },
        "AssignmentDetails": {
          "type": "object",
          "properties": {
              "AssignmentLines": {
                "type": "array",
                "properties": {
                    "Employer": {"type": "string"},
                    "ClientName": {"type": "string"},
                    "Rate": {"type": "number"},
                    "Units": {"type": "number"},
                    "Total": {"type": "number"},
                    "PaymentType": {"type": "string"},
                    "Period": {"type": "string"},
                    "DateReceived": {"type": "string"},
                    "InvoiceNo": {"type": "string"}
                }
              },
              "Expenses": {"type": "array"},
              "Mileages": {"type": "array"},
              "TotalInvoiced": {"type": "number"},
              "TotalExpenses": {"type": "number"},
              "TotalMileage": {"type": "number"},
              "TotalReceived": {"type": "number"}
          }
        },
        "PaymentSummary": {
          "type": "object",
          "properties": {
              "GrossPay": {"type": "number"},
              "EarningsForNI": {"type": "number"},
              "EarningsForTax": {"type": "number"},
              "Deductions": {"type": "number"},
              "OtherDeductions": {"type": "number"},
              "NetPay": {"type": "number"},
              "ExpensesPaid": {"type": "number"},
              "TotalPaid": {"type": "number"}
          }
        },
        "Deductions": {
          "type": "object",
          "properties": {
              "Notes": {"type": "string"},
              "Margin": {"type": "number"},
              "ApprenticeshipLevy": {"type": "number"},
              "NIERS": {"type": "number"},
              "EmploymentCosts": {"type": "number"},
              "EmployerPension": {"type": "number"},
              "HolidayPay": {"type": "number"},
              "HolidayPayCosts": {"type": "number"},
              "SalarySacrificePension": {"type": "number"},
              "ExpensesDetail": {"type": "array"},
              "MileageDetail": {"type": "array"},
              "GiftAid": {"type": "number"},
              "OtherDeductions": {"type": "array"},
              "TotalDeducted": {"type": "number"}
          }
        },
        "Worker": {
          "type": "object",
          "properties": {
              "NI": {"type": "string"},
              "NICategory": {"type": "string"},
              "TaxCode": {"type": "string"},
              "W1M1": {"type": "number"},
              "EmployeeId": {"type": "string"},
              "Email": {"type": "string"},
              "DateOfBirth": {"type": "string"}
          }
        },
        "RunDateTime": {"type": "number"},
        "AssignTotal": {"type": "number"},
        "TaxableTotal": {"type": "number"},
        "Paid": {"type": "number"},
        "Ytd": {
          "type": "object",
          "properties": {
              "GrossPayYTD": {"type": "number"},
              "EarningsForTaxYTD": {"type": "number"},
              "TaxPaidYTD": {"type": "number"},
              "EarningsForNIYTD": {"type": "number"},
              "EmployeeNIYTD": {"type": "number"},
              "EmployerNIYTD": {"type": "number"},
              "EmployeePensionYTD": {"type": "number"},
              "EmployerPensionYTD": {"type": "number"},
              "StudentLoanYTD": {"type": "number"},
              "PostgraduateLoanYTD": {"type": "number"}
          }
        },
        "Agencies": {
          "type": "object",
          "properties": {
              "AgencyName": {"type": "string"},
              "Period": {"type": "string"},
              "Amount": {"type": "number"},
              "ReceivedDate": {"type": "string"},
          }
        },
        "RecordId": {"type": "string"},
        "Comments": {"type": "array"},
        "Mileages": {"type": "array"},
        "Expenses": {"type": "array"}
    }
}
JSON;


if(isset($_GET['validate_json'])){
  try{
       $schemaObject = Schema::import(
           json_decode($schemaJson),
       )->in(
           json_decode($_POST['json_data']),
       );
       echo "JSON is valid according to the schema.";

   }catch(\Swaggest\JsonSchema\Exception\ValidationException $e){
       echo nl2br("JSON validation error: " . $e->getMessage());
       // var_dump($e);
   }catch(\Swaggest\JsonSchema\Exception\TypeException $e1){
       echo nl2br("JSON validation Type error: " . $e1->getMessage());
       // echo "<pre>";
       // var_dump($e1->inspect());
   }
   die();
}
?>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <title>Veripaye JSON schema validation tool</title>

    <style>
        *{padding:0; margin:0;}
        body{background-color:1f1f1f; color-scheme: dark;}
        input{padding:5px 10px;}
        button{padding:5px 10px;}
        button:hover{cursor:pointer;}
        a{color:white;}

        .spacer{height:100px;}
        .main_content{margin:15px auto; width:80%; max-width:600px; border:1px solid grey; color:white;}
        .content_title{position:relative; top:-10px; left:10px; background-color:1f1f1f; color:grey;}
        .content_title:after { content:"]"; }
        .content_title:before { content:"["; }
        .content{padding:20px;}
        .content a{line-height:18px;}
        .encode_msg_container, .decode_msg_container{display:none;}
        .encode_msg, .decode_msg{padding:5px 10px;}
        .error_msg{background-color:#8a0101;}
        .warn_msg{background-color:#8a4301;}
        .ok_msg{background-color:#0c8a01;}
        .footer_content{margin:0 auto; width:80%; max-width:600px; text-align:right;}

        #json_data{ width:100%; height:200px;}
        #short_url{ width:calc(100% - 160px); margin-right:10px; }
        #validate_json{ width:250px; }

        #encoded_short_url{ width:calc(100% - 160px); margin-right:10px; }
        #decode_url_btn{ width:150px; }
        #decoded_short_url{ width:100%; }
        #output{margin:0 auto; width:100%; height:200px; overflow-y:scroll; border-top:1px solid grey; border-bottom:1px solid grey;}
    </style>
</head>
<body>
<div class="spacer"></div>

<script>
$(function(){

    $('#validate_json').click(function(){
      $.ajax({
          type: "POST",
          url: "index.php?validate_json",
          cache: false,
          data: {
            "json_data": $('#json_data').val()
          },
          success: function(data){
              $("#output").empty();
              $("#output").append(data);
          }
      });
    });

});
</script>

<div class="main_content">
    <span class="content_title"> Veripaye JSON schema validation tool</span>
    <div class="content">
        <button id="validate_json">Validate JSON Sechema</button><br><br>
        <textarea id="json_data" placeholder="Enter JSON data"></textarea>
        <br><br><hr><br>
        Output
        <div id="output"></div>
    </div>
</div>

</body>
</html>
