import CryptoJS from 'crypto-js';

class EncryptionService {
    private static readonly key = "5d9831496a0db45f6ed44b42194890c1f1d233cd7fbe9d1d1c2e76dc96e5c3f2";

    static decrypt(encryptedData: string): any {
        try {
            // DECODE BASE64 STRING TO GET COMBINED IV + ENCRYPTED DATA
            const combined = CryptoJS.enc.Base64.parse(encryptedData);
            
            // EXTRACT IV (FIRST 16 BYTES)
            const iv = CryptoJS.lib.WordArray.create(combined.words.slice(0, 4));
            
            // EXTRACT ENCRYPTED DATA (REMAINING BYTES)
            const encrypted = CryptoJS.lib.WordArray.create(combined.words.slice(4));
            
            // CONVERT HEX TO WORDARRAY
            const keyHex = CryptoJS.enc.Hex.parse(this.key);

            // DECRYPT USING AES
            const decrypted = CryptoJS.AES.decrypt(
                encrypted.toString(CryptoJS.enc.Base64),
                keyHex,
                {
                    iv: iv,
                    mode: CryptoJS.mode.CBC,
                    padding: CryptoJS.pad.Pkcs7
                }
            );

            // CONVERT TO STRING AND PARSE JSON
            const decryptedStr = decrypted.toString(CryptoJS.enc.Utf8);
            return JSON.parse(decryptedStr);
        } catch (error) {
            console.error('Decryption failed:', error);
            throw error;
        }
    }
}

export default EncryptionService;
