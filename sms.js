import axios from 'axios';
require('dotenv').config();
const { SMS_USER_API_KEY , SMS_ENDPOINT , SMS_SECRET_KEY } = process.env

class SMS {
    async getToken(){
        let response = false;
        const result = await axios.post(`${SMS_ENDPOINT}/Token`, {
            "UserApiKey": SMS_USER_API_KEY,
            "SecretKey": SMS_SECRET_KEY
        })
        if (result.data.IsSuccessful === true) {
            response = result.data.TokenKey;
        } else {
            response = false;
        }
        return response;
    }
    async getCredit(){
        const TokenKey = await this.getToken()
        if(TokenKey !== false){
            const { data } = await axios({
                method: "GET",
                url: `${SMS_ENDPOINT}/credit`,
                headers: {
                    "x-sms-ir-secure-token" : TokenKey
                }
            })
            return data;
        }else{
            return false
        }
    }
    async getSmsLine(){
        const TokenKey = await this.getToken()
        if(TokenKey !== false){
            const { data } = await axios({
                method: "GET",
                url: `${SMS_ENDPOINT}/SMSLine`,
                headers: {
                    "x-sms-ir-secure-token" : TokenKey
                }
            })
            return data.SMSLines;
        }else{
            return false
        }
    }
    async sendMessage(text, mobile ){
        const TokenKey = await this.getToken()
        const SMSLines = await this.getSmsLine();
        if(TokenKey !== false && SMSLines !== false){
            const { data } = await axios({
                method: "POST",
                url: `${SMS_ENDPOINT}/MessageSend`,
                data: {
                    "Messages":[text],
                    "MobileNumbers": [mobile],
                    "LineNumber": `${SMSLines[0].LineNumber}`,
                    "SendDateTime": "",
                    "CanContinueInCaseOfError": "false",
                },
                headers: {
                    "x-sms-ir-secure-token" : TokenKey
                }
            })
            console.log(data)
            return data;
        }else{
            return false
        }
    }
    async ultraFastSend(mobile , templateId , parameters){
        const TokenKey = await this.getToken();
        if (TokenKey !== false) {

            // examples
            // const data = [
            //     {"Parameter" : "customer","ParameterValue" : "سعید مشمولی"},
            //     {"Parameter" : "link" , "ParameterValue" : "http://localhost:5000"}
            // ];
            //
            // await sms.ultraFastSend("09196426612",18597,data)


            const data = {
                "ParameterArray" : parameters,
                "Mobile" : mobile,
                "TemplateId" : templateId
            }
            const response =  await axios({
                method: "POST",
                url: `${SMS_ENDPOINT}/UltraFastSend`,
                data ,
                headers: {
                    "x-sms-ir-secure-token" : TokenKey
                }
            })
            return await response.data ;
        } else {
            return false;
        }
    }
    async sendVerificationCode(mobile , code){
        const TokenKey = await this.getToken();
        if (TokenKey !== false) {

            // examples
            // await sms.sendVerificationCode("09196426612",2132655)

            
            const data = {
                "Code" : code,
                "MobileNumber" : mobile,
            }
            await axios({
                method: "POST",
                url: `${SMS_ENDPOINT}/VerificationCode`,
                data,
                headers: {
                    "x-sms-ir-secure-token" : TokenKey
                }
            })
            return true
        } else {
            return false;
        }
    }
}
export default new SMS()