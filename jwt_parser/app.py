import os, re
from jwcrypto import jwt
from flask import Flask, request
import base64, json

app = Flask(__name__)

@app.route('/parser', methods=['POST'])
def index():
    content_type = request.headers.get('Content-Type')
    print(request.headers)
    if (content_type == 'application/json'):
        data = json.loads(request.data)
        key = jwt.JWK(**data['key'])
        enc = data['jwt']
        ET = jwt.JWT(key=key, jwt=enc)
        payload = ET.claims.split('.')[1]
        lens = len(payload)
        lenx = lens - (lens % 4 if lens % 4 else 4)
        payload += "=" * ((4 - len(payload) % 4) % 4)
        p = base64.b64decode(payload)
        print(p)
        return p
    else:
        return 'incorrect content type'

if __name__ == '__main__':
    port = int(os.environ.get('PORT', 5000))
    app.run(debug=False, host='0.0.0.0', port=port)