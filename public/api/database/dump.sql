create table socios
(
    id   int auto_increment
        primary key,
    nome varchar(150) not null
)
    comment 'Quadro societario da empresa';

create table servicos
(
    id                   int auto_increment
        primary key,
    status               int      default 0                   not null,
    socio_responsavel    int                                  null,
    valor                decimal(13, 2)                       null,
    cliente_nome         varchar(120)                         not null,
    cliente_cpfcnpj      varchar(14)                          null,
    endereco_numero      varchar(120)                         null,
    endereco_logradouro  varchar(120)                         null,
    endereco_bairro      varchar(120)                         null,
    endereco_cidade      varchar(120)                         null,
    endereco_uf          varchar(120)                         null,
    endereco_complemento varchar(120)                         null,
    contato_email        varchar(120)                         null,
    contato_fone         varchar(11)                          null,
    contato_fone2        varchar(11)                          null,
    data_criacao         datetime default current_timestamp() not null,
    constraint servicos_socios_id_fk
        foreign key (socio_responsavel) references socios (id)
            on update cascade on delete set null
);

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
);

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
);


