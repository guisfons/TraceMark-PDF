# TraceMark PDF

[Português](#português) | [English](#english)

---

## Português

**TraceMark PDF** é um plugin WordPress para distribuição segura de documentos PDF com marca d'água dinâmica e rastreável. Possui duas seções de conteúdo restrito com controle de acesso baseado no cargo **"Contributor"**.

### Funcionalidades

#### 📄 Boletim Semanal
- Post type dedicado para boletins periódicos
- **Histórico completo**: Todos os boletins ficam listados por data
- Shortcode `[boletins_semanais]` exibe cards organizados (agrupados por data)
- Identidade visual unificada com os relatórios por país
- Página sugerida: *Comitê Internacional - Boletins Semanais*

#### 🌍 Relatório por País
- Post type dedicado para relatórios por país
- **Um relatório por país** — PDF substituível, link permanente
- Taxonomy **"Países"** para organização (selecionável no admin)
- **Bandeira Editável**: Suporte para URL de imagem ou Dashicons na taxonomia
- Shortcode `[relatorios_pais]` exibe cards agrupados por país
- Visual com grid responsivo e data de última atualização

#### 🔒 Segurança e Marca d'Água
- **Acesso restrito**: Apenas usuários com cargo "Contributor" e administradores
- **Armazenamento seguro**: PDFs em diretório protegido (`wp-content/uploads/tracemark-secure/`)
- **Marca d'água de Fundo**: Texto diagonal semi-transparente com Empresa e Email do usuário no centro
- **Marca d'água de Rodapé**: Email, empresa e data/hora (fuso Brasil) em todas as páginas
- **Logo com opacidade**: Logo da empresa centralizado com 15% de transparência
- **Rastreabilidade**: Cada download gera um PDF único com dados do usuário

#### 👤 Perfil do Usuário
- Campo **Logo da Empresa** (upload de imagem)
- Campo **Nome da Empresa** (texto)
- Dados usados automaticamente na marca d'água e no frontend

### Instalação
```bash
composer install
```

### Como Usar
1. **Ativar o plugin** no painel WordPress
2. **Cadastrar Países** em *Relatórios por País > Países*
3. **Criar documentos** nos menus "Boletins Semanais" ou "Relatórios por País"
4. **Criar páginas** com os shortcodes `[boletins_semanais]` e `[relatorios_pais]`
5. **Configurar representantes**: Usuários com cargo "Contributor" acessam em *Perfil* para subir logo e nome da empresa

### Shortcodes

| Shortcode | Descrição |
|-----------|-----------|
| `[boletins_semanais]` | Grid de cards com histórico de boletins |
| `[relatorios_pais]` | Grid de cards por país (com bandeiras editáveis) |

---

## English

**TraceMark PDF** is a WordPress plugin for secure PDF distribution with dynamic, traceable watermarking. It features two restricted content sections with role-based access control for the **"Contributor"** role.

### Features

#### 📄 Weekly Bulletin
- Dedicated post type for periodic bulletins
- **Full history**: All bulletins listed by date
- Shortcode `[boletins_semanais]` displays an organized table (Date | Document | Action)

#### 🌍 Country Report
- Dedicated post type for per-country reports
- **One report per country** — replaceable PDF, permanent link
- **"Countries"** taxonomy for organization
- Shortcode `[relatorios_pais]` displays cards grouped by country

#### 🔒 Security & Watermarking
- **Restricted access**: Contributors and administrators only
- **Secure storage**: PDFs in protected directory
- **Dynamic watermark**: Email, company and date/time (Brazil timezone) on all pages
- **Logo overlay**: Company logo centered at 30% opacity
- **Traceability**: Each download generates a unique PDF with user data

### Installation
```bash
composer install
```

### How to Use
1. **Activate the plugin** in WordPress dashboard
2. **Register Countries** under *Country Reports > Countries*
3. **Create documents** using "Weekly Bulletins" or "Country Reports" menus
4. **Create pages** with `[boletins_semanais]` and `[relatorios_pais]` shortcodes
5. **Configure representatives**: Users with "Contributor" role go to *Profile* to upload logo and company name