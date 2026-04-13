#!/usr/bin/env python3
import os

filepath = os.path.join(os.path.dirname(__file__), 'environment.xml')

with open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

# Replace all &quot; with regular quotes
original_count = content.count('&quot;')
content = content.replace('&quot;', '"')

with open(filepath, 'w', encoding='utf-8') as f:
    f.write(content)

print(f"✓ Fixed: Replaced {original_count} &quot; entities with regular quotes")
