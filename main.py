import os
import subprocess
from flask import Flask, request, send_from_directory, redirect

app = Flask(__name__)

@app.route('/', defaults={'path': ''})
@app.route('/<path:path>')
def serve_php(path):
    if path == '':
        path = 'index.php'
    elif not os.path.exists(path) and not path.endswith('.php'):
        if os.path.exists(f"{path}.php"):
            path = f"{path}.php"
    
    if not os.path.exists(path):
        return "File not found", 404
    
    if path.endswith('.php'):
        try:
            output = subprocess.check_output(['php', path], universal_newlines=True)
            return output
        except subprocess.CalledProcessError as e:
            return f"Error executing PHP: {e}", 500
    else:
        # For static files
        directory = os.path.dirname(path)
        filename = os.path.basename(path)
        return send_from_directory(directory if directory else '.', filename)

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=True)