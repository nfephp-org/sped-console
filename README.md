# Sped Console

Essa biblioteca contém comandos a serem executados a partir do shell.

### Instalação

```text
composer require nfephp-org/sped-console
```

Atualmente possui os seguintes comandos:

* xsd:generate:php

### xsd:generate:php

```shell
./vendor/bin/sped xsd:generate:php schemas/NFe/PL_008g/nfe_v3.10.xsd --namespace=NFePHP\NFe\ --dest=../sped-nfe/src/NFe
```

Este comando irá gerar as classes que representam o "Objeto" NFe, baseadas na definição do arquivo xsd informado no diretório `../sped-nfe/src/NFe` definido no parâmetro `--dest`


O mesmo vale para CTe, MDFe ou CLe