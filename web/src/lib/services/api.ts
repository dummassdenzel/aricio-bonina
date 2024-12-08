import EncryptionService from './encryption';

const BASE_URL = "http://localhost/aricio-bonina/api";

async function fetchWithAuth(endpoint: string, options: RequestInit = {}) {
  const token = localStorage.getItem("token");

  const headers = {
    "Content-Type": "application/json",
    ...(token && { Authorization: `Bearer ${token}` }),
    ...options.headers,
  };

  const response = await fetch(`${BASE_URL}/${endpoint}`, {
    ...options,
    headers,
  });

  if (!response.ok) {
    const errorData = await response.json();
    throw new Error(errorData.status?.message || 'Request failed');
  }

  const jsonResponse = await response.json();
  // console.log('Raw API response:', jsonResponse);

  // DECRYPT PAYLOAD GIVEN THAT ITS CONVERTED TO STRING
  if (jsonResponse.payload && typeof jsonResponse.payload === 'string') {
      jsonResponse.payload = EncryptionService.decrypt(jsonResponse.payload);
      // console.log('Decrypted payload:', jsonResponse.payload);
    
  }

  return jsonResponse;
}

export const api = {
  get: (endpoint: string) => fetchWithAuth(endpoint),
  post: (endpoint: string, data: any) =>
    fetchWithAuth(endpoint, {
      method: "POST",
      body: JSON.stringify(data),
    }),
  postFormData: async (endpoint: string, formData: FormData) => {
    try {
        const response = await fetch(`${BASE_URL}/${endpoint}`, {
            method: 'POST',
            body: formData,
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        return data;
    } catch (error) {
        console.error('API Error:', error);
        throw error;
    }
  },
};
