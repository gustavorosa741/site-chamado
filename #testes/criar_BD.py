from sqlalchemy import Column, Integer, String, Date, Text, ForeignKey
from sqlalchemy.orm import relationship
from sqlalchemy import create_engine
from sqlalchemy.orm import declarative_base, sessionmaker
from dotenv import load_dotenv
import os
from werkzeug.security import generate_password_hash
from enum import Enum as PythonEnum
from sqlalchemy import Enum

load_dotenv()

usuario = os.getenv("USUARIO")
senha = os.getenv("SENHA")

engine = create_engine(f"mysql+pymysql://{usuario}:{senha}@localhost:3306/chamados")

Base = declarative_base()
Session = sessionmaker(bind=engine)
session = Session()

class Chamado(Base):
    __tablename__ = 'chamado'
    
    id = Column(Integer, primary_key=True)
    id_funcionario = Column(Integer, ForeignKey('usuario.id'))
    id_maquina = Column(Integer, ForeignKey('maquina.id'))
    categoria = Column(Integer, ForeignKey('categoria_chamado.id'), nullable=False)
    data_abertura = Column(Date, nullable=False)
    data_fechamento = Column(Date)
    problema = Column(Text, nullable=False)
    solucao = Column(Text)
    progresso = Column(String(200), nullable=False)
    urgencia = Column(String(200), nullable=False)


class Maquina(Base):
    __tablename__ = 'maquina'
    
    id = Column(Integer, primary_key=True)
    nome_maquina = Column(String(200), nullable=False)
    setor = Column(String(200), nullable=False)

class Usuario(Base):
    __tablename__ = 'usuario'
    
    id = Column(Integer, primary_key=True)
    nome = Column(String(200), nullable=False)
    usuario = Column(String(50), unique=True, nullable=False)
    senha = Column(String(200), nullable=False)
    nivel_acesso = Column(Integer, default=1)
    
    
    def set_senha(self, senha):
        self.senha = generate_password_hash(senha)

class CategoriaChamado(Base):
    __tablename__ = 'categoria_chamado'
    
    id = Column(Integer, primary_key=True)
    categoria = Column(String(200), nullable=False)



def create_tables():
    Base.metadata.create_all(engine)

def main():
    create_tables()
    print("Tabelas criadas com sucesso!")

if __name__ == "__main__":
    main()