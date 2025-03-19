from flask import Flask, redirect

app = Flask(__name__)

@app.route('/')
def index():
    return redirect('/index.php')

@app.route('/<path:path>')
def catch_all(path):
    return redirect(f'/{path}')

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000)