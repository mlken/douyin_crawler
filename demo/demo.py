#!env python
# -*- coding: UTF-8 -*-

import requests

data = '{"token":"36ea7692e261cc32f593b2cd7eb7dc6c","type":"crawler_search_user","search":"yesterday once more","num":20}'
response = requests.post('https://service.yundou.me/', data=data)

print(response)