import sys

with open(r'c:\xampp1\htdocs\projetadmin\views\frontoffice\login.php', 'r', encoding='utf-8') as f:
    lines = f.readlines()

new_lines = []
i = 0
while i < len(lines):
    line = lines[i]
    
    if 'id="loginFormCard"' in line:
        new_lines.append('<?php if (): ?>\n')
        new_lines.append('  <!-- ---- FORMULAIRE CONNEXION ---- -->\n')
        new_lines.append('  <div class="form-card" id="loginFormCard">\n')
        i += 1
        continue
        
    if 'showAuthForm(\'register\')' in line:
        new_lines.append(line.replace('#" onclick="showAuthForm(\'register\'); return false;', '<?= BASE_URL ?>controllers/UserController.php?action=showRegister"'))
        i += 1
        continue

    if 'id="registerFormCard"' in line:
        new_lines.pop() 
        while i < len(lines) and '</section>' not in lines[i]:
            i += 1
        i += 1 
        new_lines.append('</div>\n</div>\n<?php endif; ?>\n')
        continue

    if '<div class="form-section">' in line:
        new_lines.append('<?php if (): ?>\n')
        new_lines.append(line)
        i += 1
        continue

    if 'id="registerForm"' in line:
        new_lines.append('        <div class="form-card" id="registerForm">\n')
        i += 1
        continue

    if '<!-- ----------- CONNEXION ----------- -->' in line:
        while i < len(lines) and 'Pas encore inscrit ?' not in lines[i]:
            i += 1
        while i < len(lines) and '</div>' not in lines[i]:
            i += 1
        i += 1 
        new_lines.append('<?php endif; ?>\n')
        continue

    new_lines.append(line)
    i += 1

with open(r'c:\xampp1\htdocs\projetadmin\views\frontoffice\login.php', 'w', encoding='utf-8') as f:
    f.writelines(new_lines)
print('Done!')
