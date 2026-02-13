# TraceMark PDF

[Português](#português) | [English](#english)

---

## Português

**TraceMark PDF** é um plugin WordPress desenvolvido para a distribuição segura de documentos PDF. Ele permite que todos os usuários com o cargo (role) **"Contributor"** (frequentemente usado com Ultimate Member) acessem PDFs específicos, aplicando automaticamente uma marca d'água dinâmica baseada no perfil individual de cada usuário.

### Funcionalidades Principais
- **PDFs Restritos**: Tipo de postagem personalizada (CPT) para gerenciar documentos protegidos.
- **Acesso Baseado em Cargo**: Acesso automático para todos os usuários com a role **"Contributor"**.
- **Armazenamento Seguro**: PDFs originais são armazenados em um diretório protegido (`wp-content/uploads/tracemark-secure/`) com acesso direto bloqueado.
- **Perfil do Usuário Personalizado**: Campos no perfil do usuário para gerenciar o **Logo da Empresa** e o **Nome da Empresa**.
- **Marca d'água Dinâmica**: Aplica Email, Nome da Empresa e data/hora no rodapé de todas as páginas.
- **Logo Centralizado**: O logo do usuário é aplicado de forma centralizada com 30% de opacidade em todas as páginas.

### Instalação
O plugin requer as bibliotecas `fpdf` e `fpdi`. Se estiver instalando manualmente:
```bash
composer install
```

### Como Usar
1. **Configurar Representante**: O usuário (com cargo Contributor) deve ir em `Usuários > Perfil` para subir seu logo e preencher o nome da empresa.
2. **Postar Documento**: Administradores criam novos PDFs em `PDFs Restritos`. Não é mais necessário selecionar usuários individualmente.
3. **Download**: Qualquer usuário Logado como Contributor verá o botão de download. O PDF gerado será personalizado com os dados dele.

---

## English

**TraceMark PDF** is a WordPress plugin designed for the secure distribution of PDF documents. it allows all users with the **"Contributor"** role (commonly used with Ultimate Member) to access restricted PDFs, automatically applying a dynamic watermark based on each individual user's profile.

### Core Features
- **Restricted PDFs**: Custom Post Type (CPT) to manage protected documents.
- **Role-Based Access**: Automatic access for all users with the **"Contributor"** role.
- **Secure Storage**: Original PDFs are stored in a protected directory (`wp-content/uploads/tracemark-secure/`) with direct access blocked.
- **Custom User Profile**: Fields in the user profile to manage **Company Logo** and **Company Name**.
- **Dynamic Watermarking**: Applies Email, Company Name, and date/time in the footer of all pages.
- **Centered Logo**: The user's logo is applied centered with 30% opacity on all pages.

### Installation
The plugin requires `fpdf` and `fpdi` libraries. If installing manually:
```bash
composer install
```

### How to Use
1. **Configure Representative**: The user (with Contributor role) goes to `Users > Profile` to upload their logo and fill in the company name.
2. **Post Document**: Administrators create new PDFs in `Restricted PDFs`. Individual user selection is no longer required.
3. **Download**: Any logged-in Contributor will see the download button. The generated PDF will be personalized with their data.