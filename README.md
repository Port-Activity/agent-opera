# Opera agent

To run do something like:
```
SSH_HOST=<host> SSH_USER=<user> SSH_PASSWORD=<password> API_KEY=6cafa6559364606de1c03563b42272879af1b7699d2f7b1f8e API_URL=http://localhost:8000/agent/rest/timestamps make run
```


## Good to know
Files are transferred from Opera => FTP.
From FTP this agent will get files, parse then and port to Port Activity App.

Files are named like:
```
    MOUNKETD%48849%NA%9202558%20191231084125368338.csv
```

Where
```
    MO  = is just a prefix for all files
    UNK = refers to Unikie
```
And
```
    OPE = end of cargo operations (operoinnin loppu)
    OPS = start of cargo operations (operoinnin alku)
    ETD = Estimate time of departure (arvioitu lähtöaika)
```

File contains something like this:
```
    SPAARNEGRACHT;9202558;20193112084125;20200201230500
```
Where last date is the date that should be used as timestamp.


### More about file contents

```
EDT:
vesselname
imo-numero
timestamp (sanoman lähetyksen aikaleima)
timestamp (arvioitu lähtöaika ETD)



OPS:
vesselname
imo-numero
timestamp (sanoman lähetyksen aikaleima)
timestamp (operoinnin aloitusaika)
```


## Install ssh to php locally
You need ssh2 extension for php. If running local php install ssh2 extension with command:
```
pecl install ssh2-1.2
```

