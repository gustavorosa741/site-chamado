import random
from datetime import datetime, timedelta
from sqlalchemy import create_engine
from sqlalchemy.orm import sessionmaker
from criar_BD import Base, Chamado, Maquina, Usuario, CategoriaChamado  # Importe suas classes
from dotenv import load_dotenv
import os


load_dotenv()

usuario = os.getenv("USUARIO")
senha = os.getenv("SENHA")

# Configuração do banco de dados
engine = create_engine(f"mysql+pymysql://{usuario}:{senha}@localhost:3306/chamados")
Session = sessionmaker(bind=engine)
session = Session()

# Dados predefinidos para randomização
PROBLEMAS_PREDEFINIDOS = [
    "erro material",
    "problema de software",
    "falha no sistema",
    "defeito na máquina",
    "erro de configuração",
    "problema de rede",
    "hardware com defeito",
    "atualização necessária"
]

SOLUCOES_PREDEFINIDAS = [
    "espacando o funcionario",
    "reinstalação do software",
    "substituição de peça",
    "atualização do sistema",
    "reconfiguração completa",
    "limpeza e manutenção",
    "troca de componentes"
]

PROGRESSOS_PREDEFINIDOS = ["Concluido", "Em andamento", "Espera", "Aberto"]
URGENCIAS_PREDEFINIDAS = ["Alta", "Normal", "Baixa", "Urgente"]

def gerar_data_aleatoria(inicio=datetime(2024, 1, 1), fim=datetime(2025, 12, 31)):
    """Gera uma data aleatória entre o período especificado"""
    delta = fim - inicio
    dias_aleatorios = random.randint(0, delta.days)
    return inicio + timedelta(days=dias_aleatorios)

def gerar_chamados_aleatorios(quantidade=10):
    """Gera e insere chamados aleatórios no banco de dados"""
    
    # Buscar IDs existentes no banco
    usuarios_ids = [usuario.id for usuario in session.query(Usuario.id).all()]
    maquinas_ids = [maquina.id for maquina in session.query(Maquina.id).all()]
    categorias_ids = [categoria.id for categoria in session.query(CategoriaChamado.id).all()]
    
    if not usuarios_ids or not maquinas_ids or not categorias_ids:
        print("Erro: É necessário ter usuários, máquinas e categorias cadastradas primeiro!")
        return
    
    chamados_gerados = []
    
    for i in range(quantidade):
        # Gerar dados aleatórios
        id_funcionario = random.choice(usuarios_ids)
        id_maquina = random.choice(maquinas_ids)
        categoria = random.choice(categorias_ids)
        
        data_abertura = gerar_data_aleatoria()
        
        # 70% de chance de ter data de fechamento
        if random.random() < 0.7:
            data_fechamento = gerar_data_aleatoria(inicio=data_abertura)
        else:
            data_fechamento = None
        
        problema = random.choice(PROBLEMAS_PREDEFINIDOS)
        solucao = random.choice(SOLUCOES_PREDEFINIDAS) if data_fechamento else None
        progresso = random.choice(PROGRESSOS_PREDEFINIDOS)
        urgencia = random.choice(URGENCIAS_PREDEFINIDAS)
        
        # Criar objeto Chamado
        chamado = Chamado(
            id_funcionario=id_funcionario,
            id_maquina=id_maquina,
            categoria=categoria,
            data_abertura=data_abertura,
            data_fechamento=data_fechamento,
            problema=problema,
            solucao=solucao,
            progresso=progresso,
            urgencia=urgencia
        )
        
        chamados_gerados.append(chamado)
        session.add(chamado)
    
    try:
        session.commit()
        print(f"{quantidade} chamados gerados e inseridos com sucesso!")
        
        # Mostrar os dados gerados
        print("\nChamados gerados:")
        for i, chamado in enumerate(chamados_gerados, 1):
            print(f"{i}. Funcionario: {chamado.id_funcionario}, Máquina: {chamado.id_maquina}, "
                  f"Categoria: {chamado.categoria}, Data Abertura: {chamado.data_abertura}, "
                  f"Progresso: {chamado.progresso}, Urgência: {chamado.urgencia}")
                  
    except Exception as e:
        session.rollback()
        print(f"Erro ao inserir dados: {e}")

def popular_tabelas_base():
    """Popula as tabelas base com dados de exemplo se estiverem vazias"""
    
    # Verificar e popular Máquinas
    if session.query(Maquina).count() == 0:
        maquinas = [
            Maquina(nome_maquina="Máquina A", setor="Produção"),
            Maquina(nome_maquina="Máquina B", setor="Montagem"),
            Maquina(nome_maquina="Computador 1", setor="Administrativo"),
            Maquina(nome_maquina="Impressora Laser", setor="Expedição")
        ]
        session.add_all(maquinas)
        print("Máquinas base adicionadas")
    
    # Verificar e popular Categorias
    if session.query(CategoriaChamado).count() == 0:
        categorias = [
            CategoriaChamado(categoria="Hardware"),
            CategoriaChamado(categoria="Software"),
            CategoriaChamado(categoria="Rede"),
            CategoriaChamado(categoria="Manutenção Preventiva")
        ]
        session.add_all(categorias)
        print("Categorias base adicionadas")
    
    # Verificar e popular Usuários
    if session.query(Usuario).count() == 0:
        usuarios = [
            Usuario(nome="João Silva", usuario="joao.silva", nivel_acesso=1),
            Usuario(nome="Maria Santos", usuario="maria.santos", nivel_acesso=2),
            Usuario(nome="Pedro Oliveira", usuario="pedro.oliveira", nivel_acesso=1),
            Usuario(nome="Ana Costa", usuario="ana.costa", nivel_acesso=3)
        ]
        
        # Definir senhas padrão
        for usuario in usuarios:
            usuario.set_senha("senha123")
        
        session.add_all(usuarios)
        print("Usuários base adicionados")
    
    try:
        session.commit()
        print("Tabelas base populadas com sucesso!")
    except Exception as e:
        session.rollback()
        print(f"Erro ao popular tabelas base: {e}")

# Execução principal
if __name__ == "__main__":
    print("Iniciando geração de dados aleatórios...")
    
    # Primeiro, popula as tabelas base se necessário
    popular_tabelas_base()
    
    # Gera os chamados aleatórios
    gerar_chamados_aleatorios(10)
    
    session.close()
    print("Processo concluído!")