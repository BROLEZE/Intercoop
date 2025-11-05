// Variáveis globais
let currentContactId = null;
let clientsData = [];
let contactsFilter = 'todos';
let currentEditingClient = null;
let currentEditingProject = null;

// Inicialização
document.addEventListener('DOMContentLoaded', function() {
    loadDashboardStats();
    loadRecentActivity();
    loadClients();
    loadProjects();
    loadInformations();
    loadContacts();
    initChart();
});

// Navegação entre seções
function showSection(sectionName) {
    // Esconder todas as seções
    document.querySelectorAll('.admin-section').forEach(section => {
        section.classList.remove('active');
    });
    
    // Remover classe active de todos os links
    document.querySelectorAll('.sidebar-menu a').forEach(link => {
        link.classList.remove('active');
    });
    
    // Mostrar seção selecionada
    document.getElementById(sectionName).classList.add('active');
    
    // Adicionar classe active ao link clicado
    event.target.classList.add('active');
    
    // Atualizar título da página
    const titles = {
        'dashboard': 'Dashboard',
        'clientes': 'Gerenciar Clientes',
        'projetos': 'Gerenciar Projetos',
        'informacoes': 'Gerenciar Informações',
        'contatos': 'Contatos Recebidos',
        'relatorios': 'Relatórios'
    };
    
    document.getElementById('page-title').textContent = titles[sectionName] || 'Dashboard';
}

// Dashboard Stats
function loadDashboardStats() {
    fetch('../php/admin_get_stats.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('total-clientes').textContent = data.stats.total_clientes;
                document.getElementById('total-projetos').textContent = data.stats.total_projetos;
                document.getElementById('total-contatos').textContent = data.stats.total_contatos;
                document.getElementById('novos-mes').textContent = data.stats.novos_mes;
            }
        })
        .catch(error => console.error('Erro ao carregar estatísticas:', error));
}

// Atividade Recente
function loadRecentActivity() {
    fetch('../php/admin_get_recent_activity.php')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('recent-activities');
            
            if (data.success && data.activities.length > 0) {
                container.innerHTML = data.activities.map(activity => `
                    <div class="activity-item">
                        <h4>${activity.titulo}</h4>
                        <p>${activity.descricao} - ${formatDate(activity.data)}</p>
                    </div>
                `).join('');
            } else {
                container.innerHTML = '<p>Nenhuma atividade recente.</p>';
            }
        })
        .catch(error => console.error('Erro ao carregar atividades:', error));
}

// Carregar Clientes
function loadClients() {
    fetch('../php/admin_get_clients.php')
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('clientes-table-body');
            clientsData = data.clients || [];
            
            if (data.success && data.clients.length > 0) {
                tbody.innerHTML = data.clients.map(client => `
                    <tr>
                        <td>${client.id}</td>
                        <td>${client.nome}</td>
                        <td>${client.email}</td>
                        <td>${client.empresa || '-'}</td>
                        <td>${client.telefone || '-'}</td>
                        <td>${formatDate(client.data_cadastro)}</td>
                        <td><span class="status-badge status-${client.ativo ? 'ativo' : 'inativo'}">${client.ativo ? 'Ativo' : 'Inativo'}</span></td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="editClient(${client.id})" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-${client.ativo ? 'danger' : 'success'}" onclick="toggleClientStatus(${client.id}, ${client.ativo})" title="${client.ativo ? 'Desativar' : 'Ativar'}">
                                <i class="fas fa-${client.ativo ? 'ban' : 'check'}"></i>
                            </button>
                        </td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="8">Nenhum cliente encontrado.</td></tr>';
            }
            
            // Atualizar selects de clientes nos modais
            updateClientSelects();
        })
        .catch(error => {
            console.error('Erro ao carregar clientes:', error);
            document.getElementById('clientes-table-body').innerHTML = '<tr><td colspan="8">Erro ao carregar dados.</td></tr>';
        });
}

// Carregar Projetos
function loadProjects() {
    fetch('../php/admin_get_projects.php')
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('projetos-table-body');
            
            if (data.success && data.projects.length > 0) {
                tbody.innerHTML = data.projects.map(project => `
                    <tr>
                        <td>${project.id}</td>
                        <td>${project.codigo_projeto}</td>
                        <td>${project.nome_projeto}</td>
                        <td>${project.cliente_nome}</td>
                        <td><span class="status-badge status-${project.status}">${project.status}</span></td>
                        <td>${formatDate(project.data_criacao)}</td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="editProject(${project.id})" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteProject(${project.id})" title="Excluir">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="7">Nenhum projeto encontrado.</td></tr>';
            }
        })
        .catch(error => {
            console.error('Erro ao carregar projetos:', error);
            document.getElementById('projetos-table-body').innerHTML = '<tr><td colspan="7">Erro ao carregar dados.</td></tr>';
        });
}

// Carregar Informações
function loadInformations() {
    fetch('../php/admin_get_informations.php')
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('informacoes-table-body');
            
            if (data.success && data.informations.length > 0) {
                tbody.innerHTML = data.informations.map(info => `
                    <tr>
                        <td>${info.id}</td>
                        <td>${info.cliente_nome}</td>
                        <td>${info.informacoes.substring(0, 100)}${info.informacoes.length > 100 ? '...' : ''}</td>
                        <td>${formatDate(info.data_criacao)}</td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="viewInformation(${info.id})" title="Visualizar">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteInformation(${info.id})" title="Excluir">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="5">Nenhuma informação encontrada.</td></tr>';
            }
        })
        .catch(error => {
            console.error('Erro ao carregar informações:', error);
            document.getElementById('informacoes-table-body').innerHTML = '<tr><td colspan="5">Erro ao carregar dados.</td></tr>';
        });
}

// Carregar Contatos
function loadContacts() {
    fetch('../php/admin_get_contacts.php')
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('contatos-table-body');
            
            if (data.success && data.contacts.length > 0) {
                const filteredContacts = contactsFilter === 'todos' 
                    ? data.contacts 
                    : data.contacts.filter(contact => contact.status === contactsFilter);
                
                tbody.innerHTML = filteredContacts.map(contact => `
                    <tr>
                        <td>${contact.id}</td>
                        <td>${contact.nome}</td>
                        <td>${contact.email}</td>
                        <td>${contact.telefone}</td>
                        <td>${contact.assunto}</td>
                        <td>${formatDate(contact.data_envio)}</td>
                        <td><span class="status-badge status-${contact.status}">${contact.status}</span></td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="viewContact(${contact.id})" title="Visualizar">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteContact(${contact.id})" title="Excluir">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="8">Nenhum contato encontrado.</td></tr>';
            }
        })
        .catch(error => {
            console.error('Erro ao carregar contatos:', error);
            document.getElementById('contatos-table-body').innerHTML = '<tr><td colspan="8">Erro ao carregar dados.</td></tr>';
        });
}

// Filtrar Contatos
function filterContacts(status) {
    contactsFilter = status;
    
    // Atualizar botões ativos
    document.querySelectorAll('.filter-buttons .btn').forEach(btn => {
        btn.classList.remove('btn-primary');
        btn.classList.add('btn-outline');
    });
    
    event.target.classList.remove('btn-outline');
    event.target.classList.add('btn-primary');
    
    loadContacts();
}

// Modals
function showAddClientModal() {
    currentEditingClient = null;
    document.getElementById('addClientModal').querySelector('h3').textContent = 'Adicionar Novo Cliente';
    document.getElementById('addClientForm').reset();
    document.getElementById('addClientModal').style.display = 'block';
}

function showAddProjectModal() {
    currentEditingProject = null;
    document.getElementById('addProjectModal').querySelector('h3').textContent = 'Adicionar Novo Projeto';
    document.getElementById('addProjectForm').reset();
    document.getElementById('addProjectModal').style.display = 'block';
}

function showAddInfoModal() {
    document.getElementById('addInfoModal').querySelector('h3').textContent = 'Adicionar Nova Informação';
    document.getElementById('addInfoForm').reset();
    document.getElementById('addInfoModal').style.display = 'block';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
    
    // Limpar formulários
    const form = document.querySelector(`#${modalId} form`);
    if (form) form.reset();
    
    // Reset variáveis de edição
    currentEditingClient = null;
    currentEditingProject = null;
}

// Atualizar selects de clientes
function updateClientSelects() {
    const selects = ['projectCliente', 'infoCliente'];
    
    selects.forEach(selectId => {
        const select = document.getElementById(selectId);
        if (select) {
            select.innerHTML = '<option value="">Selecione um cliente</option>' +
                clientsData.map(client => 
                    `<option value="${client.email}">${client.nome} (${client.email})</option>`
                ).join('');
        }
    });
}

// Editar Cliente
function editClient(clientId) {
    const client = clientsData.find(c => c.id == clientId);
    if (!client) return;
    
    currentEditingClient = clientId;
    
    // Preencher formulário
    document.getElementById('clientNome').value = client.nome || '';
    document.getElementById('clientEmail').value = client.email || '';
    document.getElementById('clientEmpresa').value = client.empresa || '';
    document.getElementById('clientTelefone').value = client.telefone || '';
    
    // Alterar título e botão
    document.getElementById('addClientModal').querySelector('h3').textContent = 'Editar Cliente';
    document.getElementById('addClientModal').querySelector('button[type="submit"]').textContent = 'Atualizar Cliente';
    
    document.getElementById('addClientModal').style.display = 'block';
}

// Toggle Status Cliente
function toggleClientStatus(clientId, currentStatus) {
    const action = currentStatus ? 'desativar' : 'ativar';
    
    if (!confirm(`Tem certeza que deseja ${action} este cliente?`)) {
        return;
    }
    
    fetch('../php/admin_toggle_client_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            id: clientId,
            status: !currentStatus
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadClients();
            showNotification(`Cliente ${action} com sucesso!`, 'success');
        } else {
            showNotification('Erro ao alterar status do cliente: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showNotification('Erro ao alterar status do cliente.', 'error');
    });
}

// Editar Projeto
function editProject(projectId) {
    fetch(`../php/admin_get_project_details.php?id=${projectId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const project = data.project;
                currentEditingProject = projectId;
                
                // Preencher formulário
                document.getElementById('projectCliente').value = project.email || '';
                document.getElementById('projectCodigo').value = project.codigo_projeto || '';
                document.getElementById('projectNome').value = project.nome_projeto || '';
                document.getElementById('projectDescricao').value = project.descricao_projeto || '';
                document.getElementById('projectAnexos').value = project.anexos || '';
                
                // Alterar título e botão
                document.getElementById('addProjectModal').querySelector('h3').textContent = 'Editar Projeto';
                document.getElementById('addProjectModal').querySelector('button[type="submit"]').textContent = 'Atualizar Projeto';
                
                document.getElementById('addProjectModal').style.display = 'block';
            }
        })
        .catch(error => console.error('Erro ao carregar projeto:', error));
}

// Deletar Projeto
function deleteProject(projectId) {
    if (!confirm('Tem certeza que deseja excluir este projeto?')) {
        return;
    }
    
    fetch('../php/admin_delete_project.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ id: projectId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadProjects();
            showNotification('Projeto excluído com sucesso!', 'success');
        } else {
            showNotification('Erro ao excluir projeto: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showNotification('Erro ao excluir projeto.', 'error');
    });
}

// Ver Informação
function viewInformation(infoId) {
    fetch(`../php/admin_get_information_details.php?id=${infoId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const info = data.information;
                alert(`Informação completa:\n\n${info.informacoes}`);
            }
        })
        .catch(error => console.error('Erro ao carregar informação:', error));
}

// Deletar Informação
function deleteInformation(infoId) {
    if (!confirm('Tem certeza que deseja excluir esta informação?')) {
        return;
    }
    
    fetch('../php/admin_delete_information.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ id: infoId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadInformations();
            showNotification('Informação excluída com sucesso!', 'success');
        } else {
            showNotification('Erro ao excluir informação: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showNotification('Erro ao excluir informação.', 'error');
    });
}

// Ver detalhes do contato
function viewContact(contactId) {
    currentContactId = contactId;
    
    fetch(`../php/admin_get_contact_details.php?id=${contactId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const contact = data.contact;
                document.getElementById('contactDetails').innerHTML = `
                    <div class="contact-detail">
                        <label>Nome:</label>
                        <p>${contact.nome}</p>
                    </div>
                    <div class="contact-detail">
                        <label>Email:</label>
                        <p>${contact.email}</p>
                    </div>
                    <div class="contact-detail">
                        <label>Telefone:</label>
                        <p>${contact.telefone}</p>
                    </div>
                    <div class="contact-detail">
                        <label>Assunto:</label>
                        <p>${contact.assunto}</p>
                    </div>
                    <div class="contact-detail">
                        <label>Descrição:</label>
                        <p>${contact.descricao}</p>
                    </div>
                    <div class="contact-detail">
                        <label>Data de Envio:</label>
                        <p>${formatDate(contact.data_envio)}</p>
                    </div>
                    <div class="contact-detail">
                        <label>Status:</label>
                        <p><span class="status-badge status-${contact.status}">${contact.status}</span></p>
                    </div>
                `;
                
                document.getElementById('viewContactModal').style.display = 'block';
            }
        })
        .catch(error => console.error('Erro ao carregar detalhes do contato:', error));
}

// Deletar Contato
function deleteContact(contactId) {
    if (!confirm('Tem certeza que deseja excluir este contato?')) {
        return;
    }
    
    fetch('../php/admin_delete_contact.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ id: contactId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadContacts();
            showNotification('Contato excluído com sucesso!', 'success');
        } else {
            showNotification('Erro ao excluir contato: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showNotification('Erro ao excluir contato.', 'error');
    });
}

// Marcar contato como lido
function markAsRead() {
    if (!currentContactId) return;
    
    fetch('../php/admin_update_contact_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            id: currentContactId,
            status: 'lido'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeModal('viewContactModal');
            loadContacts();
            showNotification('Contato marcado como lido!', 'success');
        }
    })
    .catch(error => console.error('Erro ao atualizar status:', error));
}

// Marcar contato como respondido
function markAsAnswered() {
    if (!currentContactId) return;
    
    fetch('../php/admin_update_contact_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            id: currentContactId,
            status: 'respondido'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeModal('viewContactModal');
            loadContacts();
            showNotification('Contato marcado como respondido!', 'success');
        }
    })
    .catch(error => console.error('Erro ao atualizar status:', error));
}

// Exportar dados
function exportClients() {
    window.open('../php/admin_export_clients.php', '_blank');
}

function exportProjects() {
    window.open('../php/admin_export_projects.php', '_blank');
}

function exportContacts() {
    window.open('../php/admin_export_contacts.php', '_blank');
}

// Formulários
document.getElementById('addClientForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const url = currentEditingClient ? '../php/admin_update_client.php' : '../php/admin_add_client.php';
    
    if (currentEditingClient) {
        formData.append('id', currentEditingClient);
    }
    
    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeModal('addClientModal');
            loadClients();
            loadDashboardStats();
            showNotification(currentEditingClient ? 'Cliente atualizado com sucesso!' : 'Cliente adicionado com sucesso!', 'success');
        } else {
            showNotification('Erro: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Erro ao processar solicitação.', 'error');
    });
});

document.getElementById('addProjectForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const url = currentEditingProject ? '../php/admin_update_project.php' : '../php/admin_add_project.php';
    
    if (currentEditingProject) {
        formData.append('id', currentEditingProject);
    }
    
    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeModal('addProjectModal');
            loadProjects();
            loadDashboardStats();
            showNotification(currentEditingProject ? 'Projeto atualizado com sucesso!' : 'Projeto adicionado com sucesso!', 'success');
        } else {
            showNotification('Erro: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Erro ao processar solicitação.', 'error');
    });
});

document.getElementById('addInfoForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('../php/admin_add_information.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeModal('addInfoModal');
            loadInformations();
            showNotification('Informação adicionada com sucesso!', 'success');
        } else {
            showNotification('Erro: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Erro ao processar solicitação.', 'error');
    });
});

// Fechar modais clicando fora
window.onclick = function(event) {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
}

// Inicializar gráfico
function initChart() {
    fetch('../php/admin_get_chart_data.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const ctx = document.getElementById('cadastrosChart').getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            label: 'Cadastros por Mês',
                            data: data.values,
                            borderColor: '#2c5530',
                            backgroundColor: 'rgba(44, 85, 48, 0.1)',
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
        })
        .catch(error => console.error('Erro ao carregar dados do gráfico:', error));
}

// Funções utilitárias
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('pt-BR') + ' ' + date.toLocaleTimeString('pt-BR', {hour: '2-digit', minute: '2-digit'});
}

function showNotification(message, type = 'info') {
    // Criar elemento de notificação
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
        <span>${message}</span>
        <button onclick="this.parentElement.remove()">×</button>
    `;
    
    // Adicionar estilos se não existirem
    if (!document.querySelector('#notification-styles')) {
        const style = document.createElement('style');
        style.id = 'notification-styles';
        style.textContent = `
            .notification {
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 1rem 1.5rem;
                border-radius: 5px;
                color: white;
                display: flex;
                align-items: center;
                gap: 0.5rem;
                z-index: 10000;
                animation: slideIn 0.3s ease;
            }
            .notification-success { background: #28a745; }
            .notification-error { background: #dc3545; }
            .notification-info { background: #17a2b8; }
            .notification button {
                background: none;
                border: none;
                color: white;
                font-size: 1.2rem;
                cursor: pointer;
                margin-left: 1rem;
            }
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
        `;
        document.head.appendChild(style);
    }
    
    document.body.appendChild(notification);
    
    // Remover automaticamente após 5 segundos
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

// Responsive sidebar toggle
function toggleSidebar() {
    const sidebar = document.querySelector('.admin-sidebar');
    sidebar.classList.toggle('active');
}

// Adicionar botão de menu mobile se não existir
if (window.innerWidth <= 768) {
    const header = document.querySelector('.admin-header');
    const menuButton = document.createElement('button');
    menuButton.innerHTML = '<i class="fas fa-bars"></i>';
    menuButton.className = 'mobile-menu-btn';
    menuButton.onclick = toggleSidebar;
    menuButton.style.cssText = `
        background: none;
        border: none;
        font-size: 1.5rem;
        color: #2c5530;
        cursor: pointer;
        display: block;
    `;
    header.insertBefore(menuButton, header.firstChild);
}