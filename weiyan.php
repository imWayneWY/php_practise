<?php
    $testData1 = "[orderId] => 212939129
        [orderNumber] => INV10001
        [salesTax] => 1.00
        [amount] => 21.00
        [terminal] => 5
        [currency] => 1
        [type] => purchase
        [avsStreet] => 123 Road
        [avsZip] => A1A 2B2
        [customerCode] => CST1001
        [cardId] => 18951828182
        [cardHolderName] => John Smith
        [cardNumber] => 5454545454545454
        [cardExpiry] => 1025
        [cardCVV] => 100";

    $testData2 = "Request=Credit Card.Auth Only&Version=4022&HD.Network_Status_Byte=*&HD.Application_ID=TZAHSK!&HD.Terminal_ID=12991kakajsjas&HD.Device_Tag=000123&07.POS_Entry_Capability=1&07.PIN_Entry_Capability=0&07.CAT_Indicator=0&07.Terminal_Type=4&07.Account_Entry_Mode=1&07.Partial_Auth_Indicator=0&07.Account_Card_Number=4242424242424242&07.Account_Expiry=1024&07.Transaction_Amount=142931&07.Association_Token_Indicator=0&17.CVV=200&17.Street_Address=123 Road SW&17.Postal_Zip_Code=90210&17.Invoice_Number=INV19291";


    $testData3 = '{
        "MsgTypId": 111231232300,
        "CardNumber": "4242424242424242",
        "CardExp": 1024,
        "CardCVV": 240,
        "TransProcCd": "004800",
        "TransAmt": "57608",
        "MerSysTraceAudNbr": "456211",
        "TransTs": "180603162242",
        "AcqInstCtryCd": "840",
        "FuncCd": "100",
        "MsgRsnCd": "1900",
        "MerCtgyCd": "5013",
        "AprvCdLgth": "6",
        "RtrvRefNbr": "1029301923091239"
    }';

    $testData4 = "<?xml version='1.0' encoding='UTF-8'?>
        <Request>
            <NewOrder>
                <IndustryType>MO</IndustryType>
                <MessageType>AC</MessageType>
                <BIN>000001</BIN>
                <MerchantID>209238</MerchantID>
                <TerminalID>001</TerminalID>
                <CardBrand>VI</CardBrand>
                <CardDataNumber>5454545454545454</CardDataNumber>
                <Exp>1026</Exp>
                <CVVCVCSecurity>300</CVVCVCSecurity>
                <CurrencyCode>124</CurrencyCode>
                <CurrencyExponent>2</CurrencyExponent>
                <AVSzip>A2B3C3</AVSzip>
                <AVSaddress1>2010 Road SW</AVSaddress1>
                <AVScity>Calgary</AVScity>
                <AVSstate>AB</AVSstate>
                <AVSname>JOHN R SMITH</AVSname>
                <OrderID>23123INV09123</OrderID>
                <Amount>127790</Amount>
            </NewOrder>
        </Request>";

    /**
     * Type of the format for the data
     */
    class DataType {
        const IS_ARRAY = 0;
        const IS_QUERY = 1;
        const IS_JSON = 2;
        const IS_XML = 3;
    }

    /**
     * Match the string line with the maskKeys
     * @param string $str       The string to be matched
     * @param array $keys       The keywords to be matched
     */
    function isKeyLine($str, $keys) {
        foreach($keys as $key) {
            if (preg_match('/' . $key . '[=">\]]/i', $str)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Place sensitive information to asterisks
     * @param string $str                   The string need to be handled
     * @param emun $dataType                The type of the format of the string
     * @return string                       The string after masked
     */
    function maskSingleLine($str, $dataType) {
        if ($dataType == DataType::IS_ARRAY) {
            $parsedStr = explode(" => ", $str);
            $asterisks = array_fill(0, strlen($parsedStr[1]), "*");
            $result = $parsedStr[0] . " => " . implode("",$asterisks);
            return $result;
        } else if ($dataType == DataType::IS_QUERY) {
            $parsedStr = explode("=", $str);
            $asterisks = array_fill(0, strlen($parsedStr[1]), "*");
            $result = $parsedStr[0] . "=" . implode("",$asterisks);
            return $result;
        } else if ($dataType == DataType::IS_JSON) {
            preg_match('/\d+/', $str, $matches);
            $asterisks = array_fill(0, strlen($matches[0]), "*");
            $result = preg_replace('/\d+/', implode("",$asterisks), $str);
            return $result;
        } else if ($dataType == DataType::IS_XML) {
            preg_match('/\<\w+\>(.*?)\<\/\w+\>/i', $str, $matches);
            $asterisks = array_fill(0, strlen($matches[1]), "*");
            $result = preg_replace('/'.$matches[1].'/', implode("",$asterisks), $str);
            return $result;
        }
    }

    /**
     * Handle information and mask the sensitive data with asterisks
     *  @param string $data                 The data need to be handled
     *  @param array additionalMaskKeys     Keys of sensitive data
     *  @return string                      The data after sensitive data has been masked
     */
    function MaskSensitiveData($data, $additionalMaskKeys = array()) {
        // parse data to array
        $lines = explode("\n", $data);

        $dataType = -1;

        $isQueryString = false;
        if(count($lines) == 1) {
            $isQueryString = true;
            $dataType = DataType::IS_QUERY;
            $lines = explode("&",$data);
        } else {
            if (substr($lines[0],0,5) == "<?xml") {
                $dataType = DataType::IS_XML;
            } else if (substr($lines[0],0,1) == "{") {
                $dataType = DataType::IS_JSON;
            } else {
                $dataType = DataType::IS_ARRAY;
            }
        }

        // handle mask keys
        $maskKeys = array("cardNumber","cardExpiry","cardCVV","CardExp","CardDataNumber","Exp","CVVCVCSecurity","Account_Card_Number","Account_Expiry","CVV");
        $maskKeys = array_merge($maskKeys, $additionalMaskKeys);

        // loop the array and handle every line
        $newLines = array();
        foreach ($lines as $line) {
            if(isKeyLine($line, $maskKeys)) {
                $newLine = maskSingleLine($line, $dataType);
                array_push($newLines, $newLine);
            } else {
                array_push($newLines, $line);
            }
        }
        $result = "";
        $isQueryString ? $result = implode("&",$newLines) : $result = implode("\n",$newLines);
        echo $result . "\n";
        return $result;
    }


    // run the test
    MaskSensitiveData($testData1, ["salesTax", "cardHolderName"]);
    MaskSensitiveData($testData2, ["Version"]);
    MaskSensitiveData($testData3, ["TransTs"]);
    MaskSensitiveData($testData4, ["AVSname"]);
    