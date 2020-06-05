create table socios
(
    id    int auto_increment
        primary key,
    nome  varchar(150)         not null,
    ativo tinyint(1) default 1 not null
)
    comment 'Quadro societario da empresa.';

create table servicos
(
    id                   int auto_increment
        primary key,
    socio_responsavel    int                                        null,
    valor                decimal(13, 2) default 0.00                not null,
    cliente_nome         varchar(120)                               not null,
    cliente_cpfcnpj      varchar(14)                                null,
    endereco_numero      varchar(120)                               null,
    endereco_logradouro  varchar(120)                               null,
    endereco_bairro      varchar(120)                               null,
    endereco_cidade      varchar(120)                               null,
    endereco_uf          varchar(120)                               null,
    endereco_complemento varchar(120)                               null,
    contato_email        varchar(120)                               null,
    contato_fone         varchar(11)                                null,
    contato_fone2        varchar(11)                                null,
    data_criacao         datetime       default current_timestamp() not null,
    data_finalizacao     datetime                                   null comment 'NULL = Em andamento; !=NULL = Concluido.',
    descricao            mediumtext                                 null comment 'Descreve o serviço que deve ser realizado',
    observacao           mediumtext                                 null comment 'Lembretes e anotações extras',
    constraint servicos_socios_id_fk
        foreign key (socio_responsavel) references socios (id)
            on update cascade on delete set null
)
    comment 'Serviços realizados pela empresa.';

create table cheques
(
    id              int auto_increment
        primary key,
    cliente         varchar(100)   null,
    banco           varchar(50)    null comment 'nome do banco',
    agencia         varchar(20)    null,
    conta           varchar(20)    null,
    tipo            int            not null comment '0=saque em dinheiro;
1=deposito em conta (cruzado/riscado);',
    numcheque       varchar(100)   null,
    valor           decimal(13, 2) null,
    data_cheque     date           null comment 'Para cheques datados, a partir desta data o dinheiro pode ser resgatado',
    data_compensado date           null comment 'Data que o dinheiro do cheque foi resgatado',
    servico         int            null comment 'Servico vinculado como forma de pagamento',
    constraint cheques_servicos_id_fk
        foreign key (servico) references servicos (id)
            on update cascade on delete set null
)
    comment 'Cheques registrados pela empresa.';

create table pagamentos
(
    id      int auto_increment
        primary key,
    tipo    int            not null comment '0=Dinheiro;
1=Crédito;
2=Débito;
3=Cheque;
4=Outro;',
    valor   decimal(13, 2) not null,
    servico int            null,
    cheque  int            null comment 'Somente transações em cheque (tipo 3). Vincula a tabela de cheques.',
    constraint pagamentos_cheques_id_fk
        foreign key (cheque) references cheques (id)
            on update cascade on delete cascade,
    constraint pagamentos_servicos_id_fk
        foreign key (servico) references servicos (id)
            on update cascade on delete cascade
)
    comment 'Transações realizadas para pagar serviços';

create table usuarios
(
    id    int auto_increment
        primary key,
    login varchar(32)                            not null,
    senha varchar(32)                            null,
    nome  varchar(255)                           null,
    email varchar(255)                           not null,
    desde datetime   default current_timestamp() not null,
    ativo tinyint(1) default 1                   not null
)
    comment 'Contas de acesso ao sistema.';

create table sessoes
(
    id       int auto_increment
        primary key,
    chave    varchar(32)                          not null,
    usuario  int                                  not null,
    ip       varchar(32)                          null,
    datahora datetime default current_timestamp() not null,
    constraint sessoes_usuarios_id_fk
        foreign key (usuario) references usuarios (id)
            on update cascade on delete cascade
)
    comment 'Logins realizados no sistema.';


