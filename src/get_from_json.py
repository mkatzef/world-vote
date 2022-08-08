import json
import sys

key = sys.argv[-1]
content = json.loads(input())
print(content[key])
