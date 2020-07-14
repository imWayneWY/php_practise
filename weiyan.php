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
     * Handle information and mask the sensitive data with asterisks
     *  @param string $data                 The data need to be handle
     *  @param array additionalMaskKeys     Keys of sensitive data
     *  @return string                      The data after sensitive data has been masked
     */
    function MaskSensitiveData($data, $additionalMaskKeys = array()) {
        // parse data to array
        $lines = explode("\n", $data);
        $isQueryString = false;
        if(count($lines) == 1) {
            $isQueryString = true;
            $lines = explode('&',$data);
        }

        // handle mask keys
        $maskKeys = array("number","exp","cvv");
        $maskKeys = array_merge($maskKeys, $additionalMaskKeys);

        // loop the array and handle every line
        $newLines = array();
        foreach ($lines as $line) {
            $newLine = "";
            if(isKeyLine($line, $maskKeys)) {
                $newLine = preg_replace('/[0-9]+/', '*', $line);
                array_push($newLines, $newLine);
            } else {
                array_push($newLines, $line);
            }
        }
        var_dump($newLines);
    }

    /**
     * Match the string line with the maskKeys
     * @param string $str       The string to be matched
     * @param array $keys       The keywords to be matched
     * @return boolean          true if the string and the keywords are matched, otherwise return false
     */
    function isKeyLine($str, $keys) {
        foreach($keys as $key) {
            if (preg_match('/' . $key . '/i', $str)) {
                return true;
            }
        }
        return false;
    }

    MaskSensitiveData($testData4);