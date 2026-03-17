import re

file_path = r'c:\Users\dina\seo_project\src\Form\PageSectionType.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Replace all occurrences of data => $existingData['field'] ?? '' with data => $this->ensureString($existingData['field'] ?? '')
pattern1 = r"data\s*=>\s*\$existingData\['([^']*)'\]\s*\?\?\s*(''|\"\")"
replacement1 = r"data => \$this->ensureString(\$existingData['\1'] ?? \2)"

# Replace all occurrences of data => $existingData['field1'] ?? $existingData['field2'] ?? ''
pattern2 = r"data\s*=>\s*\$existingData\['([^']*)'\]\s*\?\?\s*\$existingData\['([^']*)'\]\s*\?\?\s*(''|\"\")"
replacement2 = r"data => \$this->ensureString(\$existingData['\1'] ?? \$existingData['\2'] ?? \3)"

# First handle the more complex pattern (with two fallbacks)
content = re.sub(pattern2, replacement2, content)
# Then handle the simple pattern
content = re.sub(pattern1, replacement1, content)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("All fields updated to use ensureString method")
