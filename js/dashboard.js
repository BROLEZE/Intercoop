// Load user data on page load
document.addEventListener('DOMContentLoaded', function() {
    loadUserProfile();
    loadUserProjects();
    loadUserInformations();
});

function showSection(sectionName) {
    // Hide all sections
    document.querySelectorAll('.content-section').forEach(section => {
        section.classList.remove('active');
    });
    
    // Remove active class from all nav links
    document.querySelectorAll('.sidebar a').forEach(link => {
        link.classList.remove('active');
    });
    
    // Show selected section
    document.getElementById(sectionName).classList.add('active');
    
    // Add active class to clicked nav link
    event.target.classList.add('active');
}

function loadUserProfile() {
    fetch('php/get_user_data.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const user = data.user;
                document.getElementById('userName').textContent = `Bem-vindo, ${user.nome}!`;
                
                // Fill profile form
                document.getElementById('perfilNome').value = user.nome || '';
                document.getElementById('perfilEmail').value = user.email || '';
                document.getElementById('perfilEmpresa').value = user.empresa || '';
                document.getElementById('perfilTelefone').value = user.telefone || '';
                document.getElementById('perfilEndereco').value = user.endereco || '';
                document.getElementById('perfilCidade').value = user.cidade || '';
                document.getElementById('perfilEstado').value = user.estado || '';
                document.getElementById('perfilPais').value = user.pais || '';
                document.getElementById('perfilCep').value = user.cep || '';
            }
        })
        .catch(error => console.error('Error loading user data:', error));
}

function loadUserProjects() {
    fetch('php/get_user_projects.php')
        .then(response => response.json())
        .then(data => {
            const projectsList = document.getElementById('projetosList');
            
            if (data.success && data.projects.length > 0) {
                projectsList.innerHTML = data.projects.map(project => `
                    <div class="project-card">
                        <h4>${project.nome_projeto}</h4>
                        <p><strong>Código:</strong> ${project.codigo_projeto}</p>
                        <p><strong>Descrição:</strong> ${project.descricao_projeto}</p>
                        ${project.anexos ? `<p><strong>Anexos:</strong> ${project.anexos}</p>` : ''}
                    </div>
                `).join('');
            } else {
                projectsList.innerHTML = '<p>Nenhum projeto encontrado.</p>';
            }
        })
        .catch(error => {
            console.error('Error loading projects:', error);
            document.getElementById('projetosList').innerHTML = '<p>Erro ao carregar projetos.</p>';
        });
}

function loadUserInformations() {
    fetch('php/get_user_informations.php')
        .then(response => response.json())
        .then(data => {
            const informationsList = document.getElementById('informacoesList');
            
            if (data.success && data.informations.length > 0) {
                informationsList.innerHTML = data.informations.map(info => `
                    <div class="info-card">
                        <p>${info.informacoes}</p>
                        <small>Adicionado em: ${new Date(info.data_criacao).toLocaleDateString('pt-BR')}</small>
                    </div>
                `).join('');
            } else {
                informationsList.innerHTML = '<p>Nenhuma informação encontrada.</p>';
            }
        })
        .catch(error => {
            console.error('Error loading informations:', error);
            document.getElementById('informacoesList').innerHTML = '<p>Erro ao carregar informações.</p>';
        });
}

// Profile form submission
document.getElementById('perfilForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('php/atualizar_perfil.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Perfil atualizado com sucesso!');
            loadUserProfile(); // Reload user data
        } else {
            alert('Erro ao atualizar perfil: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Erro ao atualizar perfil. Tente novamente.');
    });
});