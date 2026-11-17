#!/usr/bin/env python3
"""
Fix PHP 8.1+ first-class callable syntax (...) untuk PHP 8.0 compatibility
"""

import re
import os
from pathlib import Path

def fix_php_file(filepath):
    """Fix first-class callable syntax dalam file PHP"""
    with open(filepath, 'r', encoding='utf-8', errors='ignore') as f:
        content = f.read()
    
    original = content
    
    # Pattern untuk $varname(...)
    content = re.sub(
        r'\$([a-zA-Z_][a-zA-Z0-9_]*)\(\.\.\.\)',
        lambda m: f'$' + m.group(1),
        content
    )
    
    # Pattern untuk ClassName::methodName(...)
    content = re.sub(
        r'([a-zA-Z_][a-zA-Z0-9_]*)::\$?([a-zA-Z_][a-zA-Z0-9_]*)\(\.\.\.\)',
        lambda m: f'{m.group(1)}::' + m.group(2),
        content
    )
    
    # Pattern untuk methodName(...)
    content = re.sub(
        r'([^\$a-zA-Z_])([a-zA-Z_][a-zA-Z0-9_]*)\(\.\.\.\)([,\s\)])',
        lambda m: m.group(1) + m.group(2) + m.group(3),
        content
    )
    
    # Pattern untuk $obj->method(...)
    content = re.sub(
        r'->([a-zA-Z_][a-zA-Z0-9_]*)\(\.\.\.\)',
        lambda m: '->' + m.group(1),
        content
    )
    
    if content != original:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(content)
        return True
    return False

def scan_and_fix():
    """Scan vendor directory dan fix PHP files"""
    vendor_path = Path('vendor')
    
    # Files yang diketahui problematic
    problematic_files = [
        'vendor/symfony/http-foundation/Request.php',
        'vendor/laravel/framework/src/Illuminate/Events/Dispatcher.php',
        'vendor/laravel/framework/src/Illuminate/Support/Str.php',
        'vendor/laravel/framework/src/Illuminate/Support/Collection.php',
        'vendor/symfony/http-kernel/HttpKernel.php',
        'vendor/symfony/console/Command/Command.php',
    ]
    
    fixed_count = 0
    
    for file_pattern in problematic_files:
        if os.path.exists(file_pattern):
            if fix_php_file(file_pattern):
                print(f'✓ Fixed: {file_pattern}')
                fixed_count += 1
        else:
            print(f'⚠ Not found: {file_pattern}')
    
    # Scan seluruh vendor directory
    print("\nScanning vendor directory...")
    for php_file in vendor_path.rglob('*.php'):
        if fix_php_file(str(php_file)):
            print(f'✓ Fixed: {php_file}')
            fixed_count += 1
    
    print(f"\n✨ Total files fixed: {fixed_count}")

if __name__ == '__main__':
    os.chdir('c:\\Users\\user\\GiS_Perumahan-')
    scan_and_fix()
