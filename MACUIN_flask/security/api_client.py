import os
import requests

API_BASE_URL = os.environ.get("API_BASE_URL", "http://host.docker.internal:8080")

def get_api_headers(token):
    return {"Authorization": f"Bearer {token}"} if token else {}

def login_api(username, password):
    try:
        response = requests.post(
            f"{API_BASE_URL}/token",
            data={"username": username, "password": password},
            timeout=5
        )
        return response
    except requests.exceptions.RequestException:
        return None

def fetch_data(endpoint, token):
    try:
        response = requests.get(
            f"{API_BASE_URL}{endpoint}",
            headers=get_api_headers(token),
            timeout=5
        )
        return response.json() if response.status_code == 200 else []
    except:
        return []

def post_data(endpoint, json_data, token):
    try:
        response = requests.post(
            f"{API_BASE_URL}{endpoint}",
            json=json_data,
            headers=get_api_headers(token),
            timeout=5
        )
        return response
    except requests.exceptions.RequestException:
        return None

def post_multipart(endpoint, data, files, token):
    try:
        response = requests.post(
            f"{API_BASE_URL}{endpoint}",
            data=data,
            files=files,
            headers=get_api_headers(token),
            timeout=10
        )
        return response
    except requests.exceptions.RequestException:
        return None

def patch_data(endpoint, json_data, token):
    try:
        response = requests.patch(
            f"{API_BASE_URL}{endpoint}",
            json=json_data,
            headers=get_api_headers(token),
            timeout=5
        )
        return response
    except requests.exceptions.RequestException:
        return None
