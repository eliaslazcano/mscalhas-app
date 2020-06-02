create table socios
(
    id   int auto_increment
        primary key,
    nome varchar(150) not null
)
    comment 'Quadro societario da empresa';

create table usuarios
(
    id    int auto_increment
        primary key,
    login varchar(32)                            not null,
    senha varchar(32)                            null,
    nome  varchar(255)                           null,
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


