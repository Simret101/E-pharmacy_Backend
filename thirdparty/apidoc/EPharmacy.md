---
title: EPharmacy
language_tabs:
  - shell: Shell
  - http: HTTP
  - javascript: JavaScript
  - ruby: Ruby
  - python: Python
  - php: PHP
  - java: Java
  - go: Go
toc_footers: []
includes: []
search: true
code_clipboard: true
highlight_theme: darkula
headingLevel: 2
generator: "@tarslib/widdershins v4.0.28"

---

# EPharmacy

Base URLs:

# Authentication

# Pharmacist

## GET Get list of pharmacist

GET /pharmacist

Get the list of all Pharmacist.

> Response Examples

```json
{
  "code": "54",
  "message": "Torrens depereo eveniet asper deinde auctus amplexus ater coadunatio tracto. Conservo viduo ustulo spero depulso enim tabgo harum optio. Sperno complectus cubitum libero caste.",
  "data": [
    {
      "id": "HKtb9eOkXeI3t3-s1F2Mn",
      "name": "Nicole Koss",
      "email": "Joanie2@yahoo.com"
    },
    {
      "id": "D1HtGE-MJ0OZoowmZIfJW",
      "name": "Dr. Owen Hilll",
      "email": "Clifton61@gmail.com"
    },
    {
      "id": "BtXGKoYp8fwE3CspNIjri",
      "name": "Marshall Lemke",
      "email": "Odell58@gmail.com"
    }
  ]
}
```

### Responses

|HTTP Status Code |Meaning|Description|Data schema|
|---|---|---|---|
|200|[OK](https://tools.ietf.org/html/rfc7231#section-6.3.1)|none|Inline|

### Responses Data Schema

HTTP Status Code **200**

|Name|Type|Required|Restrictions|Title|description|
|---|---|---|---|---|---|
|» code|string|true|none||none|
|» message|string|true|none||none|
|» data|[[Pharmacist](#schemapharmacist)]|true|none||none|
|»» id|string|true|none||ID|
|»» name|string|true|none||none|
|»» email|string|true|none||none|
|»» contact|string|true|none||none|
|» pagination|[[pagination](#schemapagination)]|true|none||none|
|»» page|number|true|none||none|
|»» limit|number|true|none||none|
|»» total|number|true|none||none|

## GET Search Pharmacist by Name

GET /pharmacist/

Search for pharmacist by name.

### Params

|Name|Location|Type|Required|Description|
|---|---|---|---|---|
|name|query|string| yes |none|

> Response Examples

```json
{
  "code": "40",
  "message": "Viriliter corporis vereor cinis pauper damno cupiditate tondeo arcus. Convoco adinventitias labore placeat recusandae veniam solium. Appello curvo caecus.",
  "data": [
    {
      "id": "oQlK7g7ljWuVjDRX4Rwze",
      "name": "Perry Dickens",
      "email": "Davon28@yahoo.com"
    },
    {
      "id": "JbOCF__RSMXKbamGITrS1",
      "name": "Kurt Nikolaus",
      "email": "Alvah99@yahoo.com"
    },
    {
      "id": "GGzqdEfiKKjrpYEN7cKbJ",
      "name": "Elsie Leuschke",
      "email": "Vladimir80@yahoo.com"
    }
  ]
}
```

### Responses

|HTTP Status Code |Meaning|Description|Data schema|
|---|---|---|---|
|200|[OK](https://tools.ietf.org/html/rfc7231#section-6.3.1)|none|Inline|

### Responses Data Schema

HTTP Status Code **200**

|Name|Type|Required|Restrictions|Title|description|
|---|---|---|---|---|---|
|» code|string|true|none||none|
|» message|string|true|none||none|
|» data|[[Pharmacist](#schemapharmacist)]|true|none||none|
|»» id|string|true|none||ID|
|»» name|string|true|none||none|
|»» email|string|true|none||none|
|»» contact|string|true|none||none|

## POST Signup Pharmacist

POST /signup/

Add a new pharmacist.

> Body Parameters

```json
{
  "name": "string",
  "location": "string",
  "contact": "string",
  "email": "string"
}
```

### Params

|Name|Location|Type|Required|Description|
|---|---|---|---|---|
|body|body|object| no |none|
|» name|body|string| yes |none|
|» location|body|string| yes |none|
|» contact|body|string| yes |none|
|» email|body|string| yes |none|

> Response Examples

> 200 Response

```json
{
  "code": "string",
  "message": "string",
  "data": [
    {
      "id": "string",
      "name": "string",
      "email": "string",
      "contact": "string"
    }
  ]
}
```

### Responses

|HTTP Status Code |Meaning|Description|Data schema|
|---|---|---|---|
|200|[OK](https://tools.ietf.org/html/rfc7231#section-6.3.1)|none|Inline|

### Responses Data Schema

HTTP Status Code **200**

|Name|Type|Required|Restrictions|Title|description|
|---|---|---|---|---|---|
|» code|string|true|none||none|
|» message|string|true|none||none|
|» data|[[Pharmacist](#schemapharmacist)]|true|none||none|
|»» id|string|true|none||ID|
|»» name|string|true|none||none|
|»» email|string|true|none||none|
|»» contact|string|true|none||none|

## GET Retrieve Pharmacist

GET /pharmacist/{id}/

Get details of a specific pharmacist.

### Params

|Name|Location|Type|Required|Description|
|---|---|---|---|---|
|id|path|string| yes |none|
|id|query|string| yes |The unique ID of the pharmacist.|

> Response Examples

> 200 Response

```json
{
  "code": "string",
  "message": "string",
  "data": [
    {
      "id": "string",
      "name": "string",
      "email": "string",
      "contact": "string"
    }
  ]
}
```

### Responses

|HTTP Status Code |Meaning|Description|Data schema|
|---|---|---|---|
|200|[OK](https://tools.ietf.org/html/rfc7231#section-6.3.1)|none|Inline|

### Responses Data Schema

HTTP Status Code **200**

|Name|Type|Required|Restrictions|Title|description|
|---|---|---|---|---|---|
|» code|string|true|none||none|
|» message|string|true|none||none|
|» data|[[Pharmacist](#schemapharmacist)]|true|none||none|
|»» id|string|true|none||ID|
|»» name|string|true|none||none|
|»» email|string|true|none||none|
|»» contact|string|true|none||none|

## PUT Update Pharmacist

PUT /pharmacist/{id}/

Update pharmacist details.

> Body Parameters

```json
{
  "name": "string",
  "location": "string",
  "contact": "string",
  "email": "string"
}
```

### Params

|Name|Location|Type|Required|Description|
|---|---|---|---|---|
|id|path|string| yes |The unique ID of the pharmacist.|
|body|body|object| no |none|
|» name|body|string| yes |none|
|» location|body|string| yes |none|
|» contact|body|string| yes |none|
|» email|body|string| yes |none|

> Response Examples

> 200 Response

```json
{
  "code": "string",
  "message": "string",
  "data": [
    {
      "id": "string",
      "name": "string",
      "email": "string",
      "contact": "string"
    }
  ]
}
```

### Responses

|HTTP Status Code |Meaning|Description|Data schema|
|---|---|---|---|
|200|[OK](https://tools.ietf.org/html/rfc7231#section-6.3.1)|none|Inline|

### Responses Data Schema

HTTP Status Code **200**

|Name|Type|Required|Restrictions|Title|description|
|---|---|---|---|---|---|
|» code|string|true|none||none|
|» message|string|true|none||none|
|» data|[[Pharmacist](#schemapharmacist)]|true|none||none|
|»» id|string|true|none||ID|
|»» name|string|true|none||none|
|»» email|string|true|none||none|
|»» contact|string|true|none||none|

## DELETE Delete Pharmacist

DELETE /pharmacist/{id}

Delete a pharmacist by ID.

### Params

|Name|Location|Type|Required|Description|
|---|---|---|---|---|
|id|path|string| yes |none|

> Response Examples

> 204 Response

```json
{
  "message": "string",
  "error": "string"
}
```

### Responses

|HTTP Status Code |Meaning|Description|Data schema|
|---|---|---|---|
|204|[No Content](https://tools.ietf.org/html/rfc7231#section-6.3.5)|none|Inline|

### Responses Data Schema

HTTP Status Code **204**

|Name|Type|Required|Restrictions|Title|description|
|---|---|---|---|---|---|
|» message|string|true|none||deleted successfully.|
|» error|string|true|none||none|

# Patient Management API

## POST login patient

POST /auth/login

Authenticate a user and return a JWT token.

### Params

|Name|Location|Type|Required|Description|
|---|---|---|---|---|
|email|query|string| no |none|
|password|query|string| no |none|

> Response Examples

> 200 Response

```json
{
  "code": "string",
  "message": "string",
  "data": [
    {
      "id": "string",
      "username": "string",
      "email": "string"
    }
  ]
}
```

### Responses

|HTTP Status Code |Meaning|Description|Data schema|
|---|---|---|---|
|200|[OK](https://tools.ietf.org/html/rfc7231#section-6.3.1)|none|Inline|
|400|[Bad Request](https://tools.ietf.org/html/rfc7231#section-6.5.1)|none|Inline|

### Responses Data Schema

HTTP Status Code **200**

|Name|Type|Required|Restrictions|Title|description|
|---|---|---|---|---|---|
|» code|string|true|none||none|
|» message|string|true|none||none|
|» data|[[Patient](#schemapatient)]|true|none||none|
|»» id|string|true|none||none|
|»» username|string|true|none||none|
|»» email|string|true|none||none|

HTTP Status Code **400**

|Name|Type|Required|Restrictions|Title|description|
|---|---|---|---|---|---|
|» status|string|true|none||none|
|» message|string|true|none||none|
|» error|object|true|none||none|

## POST logout patient

POST /auth/logout/

Logout the user by invalidating their JWT token.

> Response Examples

> 200 Response

```json
{
  "code": "string",
  "message": "string"
}
```

### Responses

|HTTP Status Code |Meaning|Description|Data schema|
|---|---|---|---|
|200|[OK](https://tools.ietf.org/html/rfc7231#section-6.3.1)|none|Inline|
|400|[Bad Request](https://tools.ietf.org/html/rfc7231#section-6.5.1)|none|Inline|

### Responses Data Schema

HTTP Status Code **200**

|Name|Type|Required|Restrictions|Title|description|
|---|---|---|---|---|---|
|» code|string|true|none||none|
|» message|string|true|none||none|

HTTP Status Code **400**

|Name|Type|Required|Restrictions|Title|description|
|---|---|---|---|---|---|
|» status|string|true|none||none|
|» message|string|true|none||none|
|» error|object|true|none||none|

## POST signup users

POST /patients/

Register a new patients.

> Body Parameters

```json
{
  "username": "string",
  "email": "string",
  "password": "string"
}
```

### Params

|Name|Location|Type|Required|Description|
|---|---|---|---|---|
|body|body|object| no |none|
|» username|body|string| yes |none|
|» email|body|string| yes |none|
|» password|body|string| yes |none|

> Response Examples

```json
{
  "code": "60",
  "message": "Cometes corroboro temeritas bos. Cavus vita catena decens cursus dolores hic victoria facere absconditus. Bos aliquam barba tepidus pectus thema quidem appositus censura.",
  "data": [
    {
      "id": "Fxh9oHr4w6qI6q7aSADtw",
      "username": "Florence Rowe",
      "email": "Marjorie86@yahoo.com"
    },
    {
      "id": "ioxfGpYScWrKtd-uQ2cS9",
      "username": "Geoffrey Hane",
      "email": "Tiara.Skiles89@hotmail.com"
    },
    {
      "id": "apwcIypfjdPWhq9Bg1kVK",
      "username": "Miss Tammy Wisozk",
      "email": "Jo_Kautzer@gmail.com"
    }
  ]
}
```

> 400 Response

```json
{
  "status": "string",
  "message": "string",
  "error": {}
}
```

### Responses

|HTTP Status Code |Meaning|Description|Data schema|
|---|---|---|---|
|201|[Created](https://tools.ietf.org/html/rfc7231#section-6.3.2)|none|Inline|
|400|[Bad Request](https://tools.ietf.org/html/rfc7231#section-6.5.1)|none|Inline|

### Responses Data Schema

HTTP Status Code **201**

|Name|Type|Required|Restrictions|Title|description|
|---|---|---|---|---|---|
|» code|string|true|none||none|
|» message|string|true|none||none|
|» data|[[Patient](#schemapatient)]|true|none||none|
|»» id|string|true|none||none|
|»» username|string|true|none||none|
|»» email|string|true|none||none|

HTTP Status Code **400**

|Name|Type|Required|Restrictions|Title|description|
|---|---|---|---|---|---|
|» status|string|true|none||none|
|» message|string|true|none||none|
|» error|object|true|none||none|

## GET Get all patients

GET /patients/

Get all patients.

> Response Examples

> 200 Response

```json
{
  "code": "string",
  "message": "string",
  "data": [
    {
      "id": "string",
      "username": "string",
      "email": "string"
    }
  ],
  "pagination": [
    {
      "page": 0,
      "limit": 0,
      "total": 0
    }
  ]
}
```

### Responses

|HTTP Status Code |Meaning|Description|Data schema|
|---|---|---|---|
|200|[OK](https://tools.ietf.org/html/rfc7231#section-6.3.1)|none|Inline|
|400|[Bad Request](https://tools.ietf.org/html/rfc7231#section-6.5.1)|none|Inline|

### Responses Data Schema

HTTP Status Code **200**

|Name|Type|Required|Restrictions|Title|description|
|---|---|---|---|---|---|
|» code|string|true|none||none|
|» message|string|true|none||none|
|» data|[[Patient](#schemapatient)]|true|none||none|
|»» id|string|true|none||none|
|»» username|string|true|none||none|
|»» email|string|true|none||none|
|» pagination|[[pagination](#schemapagination)]|true|none||none|
|»» page|number|true|none||none|
|»» limit|number|true|none||none|
|»» total|number|true|none||none|

HTTP Status Code **400**

|Name|Type|Required|Restrictions|Title|description|
|---|---|---|---|---|---|
|» status|string|true|none||none|
|» message|string|true|none||none|
|» error|object|true|none||none|

## GET Get a patient by id

GET /patient/{id}

Retrieve a specific patient's details.

### Params

|Name|Location|Type|Required|Description|
|---|---|---|---|---|
|id|path|string| yes |none|

> Response Examples

```json
{
  "code": "78",
  "message": "Aveho iste aedificium. Certe quibusdam vulariter stillicidium tot theologus quia. Amplitudo accusamus iusto.",
  "data": [
    {
      "id": "X6vGTbSxD27QrPm_Adhl6",
      "username": "Lynn Streich",
      "email": "Freddy40@gmail.com"
    },
    {
      "id": "QdYeRYg55b1BquX44c3pk",
      "username": "Clyde Kulas-Medhurst",
      "email": "Markus_Borer@yahoo.com"
    }
  ]
}
```

> 400 Response

```json
{
  "status": "string",
  "message": "string",
  "error": {}
}
```

### Responses

|HTTP Status Code |Meaning|Description|Data schema|
|---|---|---|---|
|200|[OK](https://tools.ietf.org/html/rfc7231#section-6.3.1)|none|Inline|
|400|[Bad Request](https://tools.ietf.org/html/rfc7231#section-6.5.1)|none|Inline|

### Responses Data Schema

HTTP Status Code **200**

|Name|Type|Required|Restrictions|Title|description|
|---|---|---|---|---|---|
|» code|string|true|none||none|
|» message|string|true|none||none|
|» data|[[Patient](#schemapatient)]|true|none||none|
|»» id|string|true|none||none|
|»» username|string|true|none||none|
|»» email|string|true|none||none|

HTTP Status Code **400**

|Name|Type|Required|Restrictions|Title|description|
|---|---|---|---|---|---|
|» status|string|true|none||none|
|» message|string|true|none||none|
|» error|object|true|none||none|

## PUT update patient

PUT /patient/{id}

Update patient's information.

### Params

|Name|Location|Type|Required|Description|
|---|---|---|---|---|
|id|path|string| yes |none|
|username|query|string| no |none|
|email|query|string| no |none|
|password|query|string| no |none|

> Response Examples

```json
{
  "code": "37",
  "message": "Animus sum tego tabgo sodalitas absum ante caveo ocer. Admoneo viscus ambulo impedit. Conventus adinventitias cresco derideo talus coruscus ubi carus cum.",
  "data": [
    {
      "id": "N1CuYsSWdTGBM4bjCdJV0",
      "username": "Elena Ritchie",
      "email": "Veronica49@hotmail.com"
    },
    {
      "id": "AcHA5vwyRYeu-mjX1Qntu",
      "username": "Fernando Parker-Keebler",
      "email": "Jessica_Auer@gmail.com"
    }
  ]
}
```

> 400 Response

```json
{
  "status": "string",
  "message": "string",
  "error": {}
}
```

### Responses

|HTTP Status Code |Meaning|Description|Data schema|
|---|---|---|---|
|200|[OK](https://tools.ietf.org/html/rfc7231#section-6.3.1)|none|Inline|
|400|[Bad Request](https://tools.ietf.org/html/rfc7231#section-6.5.1)|none|Inline|

### Responses Data Schema

HTTP Status Code **200**

|Name|Type|Required|Restrictions|Title|description|
|---|---|---|---|---|---|
|» code|string|true|none||none|
|» message|string|true|none||none|
|» data|[[Patient](#schemapatient)]|true|none||none|
|»» id|string|true|none||none|
|»» username|string|true|none||none|
|»» email|string|true|none||none|

HTTP Status Code **400**

|Name|Type|Required|Restrictions|Title|description|
|---|---|---|---|---|---|
|» status|string|true|none||none|
|» message|string|true|none||none|
|» error|object|true|none||none|

## DELETE delete patient

DELETE /patient/{id}/

Delete a patient by ID.

### Params

|Name|Location|Type|Required|Description|
|---|---|---|---|---|
|id|path|string| yes |none|

> Response Examples

> 204 Response

```json
{}
```

### Responses

|HTTP Status Code |Meaning|Description|Data schema|
|---|---|---|---|
|204|[No Content](https://tools.ietf.org/html/rfc7231#section-6.3.5)|none|Inline|
|400|[Bad Request](https://tools.ietf.org/html/rfc7231#section-6.5.1)|none|Inline|

### Responses Data Schema

HTTP Status Code **400**

|Name|Type|Required|Restrictions|Title|description|
|---|---|---|---|---|---|
|» status|string|true|none||none|
|» message|string|true|none||none|
|» error|object|true|none||none|

# Drug Management API

## GET List all drugs.

GET /drugs/

List all drugs.

> Response Examples

```json
{
  "code": "88",
  "message": "Ullam stella subseco abstergo stella sufficio. Summisse patria utor confero modi aperio. Quis capto avaritia amplexus audeo aequus cultellus subito.",
  "data": [
    {
      "id": "DwZriMJTAhDMZEIDKfruc",
      "name": "Joanne Greenholt",
      "description": "Claudeo tamquam vulpes colo tenetur cupiditas cupiditate verecundia. Quo cribro ara adhuc tenetur peior. Theatrum temeritas abundans apud canonicus repudiandae cogo cupio molestiae tendo. Verecundia cubitum benigne. Arma minima statua. Supra neque cohibeo una.",
      "price": "760.09"
    },
    {
      "id": "NompUL4S5t7X0faMjsYnc",
      "name": "Cecelia Doyle",
      "description": "Depromo tandem degenero alienus atavus perspiciatis pecco cultura anser. Tolero avaritia valeo umquam. Casus amissio pecco clarus clementia abscido conatus.",
      "price": "946.55"
    },
    {
      "id": "2M2lvjvzXXCc7IW1ZHFEI",
      "name": "Beth Botsford",
      "description": "Vestigium quasi ventito. Claro voluptas unus velum cibo. Umquam curiositas subvenio stultus pariatur cariosus cunctatio autus. Pectus catena inflammatio adfectus vesco.",
      "price": "544.64"
    }
  ],
  "pagination": [
    {
      "page": -69330226.03653036,
      "limit": 74165160.10959765,
      "total": -33367713.084677808
    }
  ]
}
```

> 400 Response

```json
{
  "status": "string",
  "message": "string",
  "error": {}
}
```

### Responses

|HTTP Status Code |Meaning|Description|Data schema|
|---|---|---|---|
|200|[OK](https://tools.ietf.org/html/rfc7231#section-6.3.1)|none|Inline|
|400|[Bad Request](https://tools.ietf.org/html/rfc7231#section-6.5.1)|none|Inline|

### Responses Data Schema

HTTP Status Code **200**

|Name|Type|Required|Restrictions|Title|description|
|---|---|---|---|---|---|
|» data|[[Drug](#schemadrug)]|true|none||none|
|»» id|string|true|none||none|
|»» name|string|true|none||none|
|»» description|string|true|none||none|
|»» price|integer|true|none||none|
|» pagination|[[pagination](#schemapagination)]|true|none||none|
|»» page|number|true|none||none|
|»» limit|number|true|none||none|
|»» total|number|true|none||none|

HTTP Status Code **400**

|Name|Type|Required|Restrictions|Title|description|
|---|---|---|---|---|---|
|» status|string|true|none||none|
|» message|string|true|none||none|
|» error|object|true|none||none|

## POST Create Drug

POST /drugs/

Add a new drug.

> Body Parameters

```json
{
  "name": "string",
  "description": "string",
  "price": 0
}
```

### Params

|Name|Location|Type|Required|Description|
|---|---|---|---|---|
|body|body|object| no |none|
|» name|body|string| yes |none|
|» description|body|string| yes |none|
|» price|body|integer| yes |none|

> Response Examples

> 201 Response

```json
{
  "code": "string",
  "message": "string",
  "data": [
    {
      "id": "string",
      "name": "string",
      "description": "string",
      "price": 0
    }
  ]
}
```

### Responses

|HTTP Status Code |Meaning|Description|Data schema|
|---|---|---|---|
|201|[Created](https://tools.ietf.org/html/rfc7231#section-6.3.2)|none|Inline|
|400|[Bad Request](https://tools.ietf.org/html/rfc7231#section-6.5.1)|none|Inline|

### Responses Data Schema

HTTP Status Code **201**

|Name|Type|Required|Restrictions|Title|description|
|---|---|---|---|---|---|
|» code|string|true|none||none|
|» message|string|true|none||none|
|» data|[[Drug](#schemadrug)]|true|none||none|
|»» id|string|true|none||none|
|»» name|string|true|none||none|
|»» description|string|true|none||none|
|»» price|integer|true|none||none|

HTTP Status Code **400**

|Name|Type|Required|Restrictions|Title|description|
|---|---|---|---|---|---|
|» status|string|true|none||none|
|» message|string|true|none||none|
|» error|object|true|none||none|

## GET Search Drugs by Name

GET /drugs/{name}

Search for drugs by name.

### Params

|Name|Location|Type|Required|Description|
|---|---|---|---|---|
|name|path|string| yes |none|
|name|query|string| yes |Name of the drug to search for.|

> Response Examples

```json
{
  "code": "22",
  "message": "Carus vero tamen. Succurro delego carus ut totidem. Tenus cicuta nemo.",
  "data": [
    {
      "id": "K6aLZO_lb20YaMrBrwZeW",
      "name": "Dr. Sylvester King",
      "description": "Defendo cilicium sperno vulgus velit porro arx altus. Magni usque calco textus compono vix caelum sustineo. Antiquus tunc vorax tumultus impedit. Amita aurum teres.",
      "price": "525.59"
    },
    {
      "id": "0r_PmO5D8m_D_nPKnBzKP",
      "name": "Ms. Jacqueline Buckridge",
      "description": "Non defleo adsum ulterius vinum adhaero sonitus occaecati. Caterva natus minima compello tempore caste acies. Vapulus fuga totam quas vulticulus. Dedecor tubineus patruus tabernus. Sub celo perspiciatis verus vulgaris canto centum officia. Aliqua cogito subseco. Clam tactus acidus deleniti audio vulticulus arguo stella.",
      "price": "123.09"
    },
    {
      "id": "TVTfJXcmxeKEtWpZ2oAlM",
      "name": "Jane Prohaska",
      "description": "Celo acies cohors cuppedia vilis comptus ciminatio condico. Defetiscor optio clam coniecto cursus vespillo aliqua teneo labore tredecim. Depraedor ager consuasor synagoga culpo textilis voluntarius. Adsuesco harum ancilla. Sono odio aperte desino attollo sono fugiat celebrer arcesso delibero.",
      "price": "994.50"
    }
  ]
}
```

> 400 Response

```json
{
  "status": "string",
  "message": "string",
  "error": {}
}
```

### Responses

|HTTP Status Code |Meaning|Description|Data schema|
|---|---|---|---|
|200|[OK](https://tools.ietf.org/html/rfc7231#section-6.3.1)|none|Inline|
|400|[Bad Request](https://tools.ietf.org/html/rfc7231#section-6.5.1)|none|Inline|

### Responses Data Schema

HTTP Status Code **200**

|Name|Type|Required|Restrictions|Title|description|
|---|---|---|---|---|---|
|» code|string|true|none||none|
|» message|string|true|none||none|
|» data|[[Drug](#schemadrug)]|true|none||none|
|»» id|string|true|none||none|
|»» name|string|true|none||none|
|»» description|string|true|none||none|
|»» price|integer|true|none||none|

HTTP Status Code **400**

|Name|Type|Required|Restrictions|Title|description|
|---|---|---|---|---|---|
|» status|string|true|none||none|
|» message|string|true|none||none|
|» error|object|true|none||none|

## GET Retrieve Drug

GET /drugs/{id}/

Get details of a specific drug.

### Params

|Name|Location|Type|Required|Description|
|---|---|---|---|---|
|id|path|string| yes |none|

> Response Examples

> 200 Response

```json
{
  "code": "string",
  "message": "string",
  "data": [
    {
      "id": "string",
      "name": "string",
      "description": "string",
      "price": 0
    }
  ]
}
```

### Responses

|HTTP Status Code |Meaning|Description|Data schema|
|---|---|---|---|
|200|[OK](https://tools.ietf.org/html/rfc7231#section-6.3.1)|none|Inline|
|400|[Bad Request](https://tools.ietf.org/html/rfc7231#section-6.5.1)|none|Inline|

### Responses Data Schema

HTTP Status Code **200**

|Name|Type|Required|Restrictions|Title|description|
|---|---|---|---|---|---|
|» code|string|true|none||none|
|» message|string|true|none||none|
|» data|[[Drug](#schemadrug)]|true|none||none|
|»» id|string|true|none||none|
|»» name|string|true|none||none|
|»» description|string|true|none||none|
|»» price|integer|true|none||none|

HTTP Status Code **400**

|Name|Type|Required|Restrictions|Title|description|
|---|---|---|---|---|---|
|» status|string|true|none||none|
|» message|string|true|none||none|
|» error|object|true|none||none|

## DELETE Delete Drug

DELETE /drugs/{id}/

Delete a drug by ID.

### Params

|Name|Location|Type|Required|Description|
|---|---|---|---|---|
|id|path|string| yes |none|

> Response Examples

> 204 Response

```json
{
  "code": "string",
  "message": "string",
  "data": {}
}
```

### Responses

|HTTP Status Code |Meaning|Description|Data schema|
|---|---|---|---|
|204|[No Content](https://tools.ietf.org/html/rfc7231#section-6.3.5)|none|Inline|
|400|[Bad Request](https://tools.ietf.org/html/rfc7231#section-6.5.1)|none|Inline|

### Responses Data Schema

HTTP Status Code **204**

|Name|Type|Required|Restrictions|Title|description|
|---|---|---|---|---|---|
|» code|string|true|none||none|
|» message|string|true|none||none|
|» data|object|true|none||none|

HTTP Status Code **400**

|Name|Type|Required|Restrictions|Title|description|
|---|---|---|---|---|---|
|» status|string|true|none||none|
|» message|string|true|none||none|
|» error|object|true|none||none|

## PUT Update Drug

PUT /drugs/{id}

Update drug details.

> Body Parameters

```json
{
  "name": "string",
  "description": "string",
  "price": 0
}
```

### Params

|Name|Location|Type|Required|Description|
|---|---|---|---|---|
|id|path|string| yes |none|
|body|body|object| no |none|
|» name|body|string| yes |none|
|» description|body|string| yes |none|
|» price|body|number| yes |none|

> Response Examples

> 200 Response

```json
{
  "code": "string",
  "message": "string",
  "data": [
    {
      "id": "string",
      "name": "string",
      "description": "string",
      "price": 0
    }
  ]
}
```

### Responses

|HTTP Status Code |Meaning|Description|Data schema|
|---|---|---|---|
|200|[OK](https://tools.ietf.org/html/rfc7231#section-6.3.1)|none|Inline|
|400|[Bad Request](https://tools.ietf.org/html/rfc7231#section-6.5.1)|none|Inline|

### Responses Data Schema

HTTP Status Code **200**

|Name|Type|Required|Restrictions|Title|description|
|---|---|---|---|---|---|
|» code|string|true|none||none|
|» message|string|true|none||none|
|» data|[[Drug](#schemadrug)]|true|none||none|
|»» id|string|true|none||none|
|»» name|string|true|none||none|
|»» description|string|true|none||none|
|»» price|integer|true|none||none|

HTTP Status Code **400**

|Name|Type|Required|Restrictions|Title|description|
|---|---|---|---|---|---|
|» status|string|true|none||none|
|» message|string|true|none||none|
|» error|object|true|none||none|

# Cart Management API

## GET View Cart

GET /cart/

View the current user's shopping cart.

> Response Examples

> 200 Response

```json
{
  "code": "string",
  "message": "string",
  "data": [
    {
      "item_id": "string",
      "drug_id": "string",
      "name": "string",
      "quantity": 0,
      "price": 0
    }
  ]
}
```

### Responses

|HTTP Status Code |Meaning|Description|Data schema|
|---|---|---|---|
|200|[OK](https://tools.ietf.org/html/rfc7231#section-6.3.1)|none|Inline|
|400|[Bad Request](https://tools.ietf.org/html/rfc7231#section-6.5.1)|none|Inline|

### Responses Data Schema

HTTP Status Code **200**

|Name|Type|Required|Restrictions|Title|description|
|---|---|---|---|---|---|
|» code|string|true|none||none|
|» message|string|true|none||none|
|» data|[[items](#schemaitems)]|true|none||none|
|»» item_id|string|true|none||none|
|»» drug_id|string|true|none||none|
|»» name|string|true|none||name|
|»» quantity|number|true|none||none|
|»» price|integer|true|none||none|

HTTP Status Code **400**

|Name|Type|Required|Restrictions|Title|description|
|---|---|---|---|---|---|
|» status|string|true|none||none|
|» message|string|true|none||none|
|» error|object|true|none||none|

## POST Add to Cart

POST /cart/

Add a drug to the cart.

> Body Parameters

```json
{
  "drug_id": "string",
  "quantity": 0
}
```

### Params

|Name|Location|Type|Required|Description|
|---|---|---|---|---|
|body|body|object| no |none|
|» drug_id|body|string| yes |none|
|» quantity|body|number| yes |none|

> Response Examples

```json
{
  "code": "8",
  "message": "Caritas tantum abduco ascit culpa uxor decens. Pauci deripio atqui temeritas sum iste. Suspendo contigo volva caelum aspicio.",
  "data": [
    {
      "item_id": "minim incididunt Duis",
      "drug_id": "nisi eu",
      "name": "Darrell Schaefer",
      "quantity": 39,
      "price": 30
    },
    {
      "item_id": "qui do proident ex occaecat",
      "drug_id": "deserunt occaecat",
      "name": "Mr. Danny Feest",
      "quantity": 67,
      "price": 295
    }
  ]
}
```

> 400 Response

```json
{
  "status": "string",
  "message": "string",
  "error": {}
}
```

### Responses

|HTTP Status Code |Meaning|Description|Data schema|
|---|---|---|---|
|201|[Created](https://tools.ietf.org/html/rfc7231#section-6.3.2)|none|Inline|
|400|[Bad Request](https://tools.ietf.org/html/rfc7231#section-6.5.1)|none|Inline|

### Responses Data Schema

HTTP Status Code **201**

|Name|Type|Required|Restrictions|Title|description|
|---|---|---|---|---|---|
|» code|string|true|none||none|
|» message|string|true|none||none|
|» data|[[items](#schemaitems)]|true|none||none|
|»» item_id|string|true|none||none|
|»» drug_id|string|true|none||none|
|»» name|string|true|none||name|
|»» quantity|number|true|none||none|
|»» price|integer|true|none||none|

HTTP Status Code **400**

|Name|Type|Required|Restrictions|Title|description|
|---|---|---|---|---|---|
|» status|string|true|none||none|
|» message|string|true|none||none|
|» error|object|true|none||none|

## DELETE Clear Cart

DELETE /cart/

Clear all items from the cart.

> Response Examples

> 200 Response

```json
{
  "code": "string",
  "message": "string"
}
```

### Responses

|HTTP Status Code |Meaning|Description|Data schema|
|---|---|---|---|
|200|[OK](https://tools.ietf.org/html/rfc7231#section-6.3.1)|none|Inline|
|400|[Bad Request](https://tools.ietf.org/html/rfc7231#section-6.5.1)|none|Inline|

### Responses Data Schema

HTTP Status Code **200**

|Name|Type|Required|Restrictions|Title|description|
|---|---|---|---|---|---|
|» code|string|true|none||none|
|» message|string|true|none||none|

HTTP Status Code **400**

|Name|Type|Required|Restrictions|Title|description|
|---|---|---|---|---|---|
|» status|string|true|none||none|
|» message|string|true|none||none|
|» error|object|true|none||none|

## PUT Update Cart Item

PUT /cart/{item_id}

Update the quantity of an item in the cart.

> Body Parameters

```json
{
  "quantity": 0,
  "drug": [
    {
      "id": "string",
      "name": "string",
      "description": "string",
      "price": 0
    }
  ]
}
```

### Params

|Name|Location|Type|Required|Description|
|---|---|---|---|---|
|item_id|path|string| yes |none|
|body|body|object| no |none|
|» quantity|body|number| yes |none|
|» drug|body|[[Drug](#schemadrug)]| yes |none|
|»» id|body|string| yes |none|
|»» name|body|string| yes |none|
|»» description|body|string| yes |none|
|»» price|body|integer| yes |none|

> Response Examples

> 200 Response

```json
{
  "code": "string",
  "message": "string",
  "data": [
    {
      "item_id": "string",
      "drug_id": "string",
      "name": "string",
      "quantity": 0,
      "price": 0
    }
  ]
}
```

### Responses

|HTTP Status Code |Meaning|Description|Data schema|
|---|---|---|---|
|200|[OK](https://tools.ietf.org/html/rfc7231#section-6.3.1)|none|Inline|
|400|[Bad Request](https://tools.ietf.org/html/rfc7231#section-6.5.1)|none|Inline|

### Responses Data Schema

HTTP Status Code **200**

|Name|Type|Required|Restrictions|Title|description|
|---|---|---|---|---|---|
|» code|string|true|none||none|
|» message|string|true|none||none|
|» data|[[items](#schemaitems)]|true|none||none|
|»» item_id|string|true|none||none|
|»» drug_id|string|true|none||none|
|»» name|string|true|none||name|
|»» quantity|number|true|none||none|
|»» price|integer|true|none||none|

HTTP Status Code **400**

|Name|Type|Required|Restrictions|Title|description|
|---|---|---|---|---|---|
|» status|string|true|none||none|
|» message|string|true|none||none|
|» error|object|true|none||none|

## DELETE Remove from Cart

DELETE /cart/{item_id}/

Remove an item from the cart.

### Params

|Name|Location|Type|Required|Description|
|---|---|---|---|---|
|item_id|path|string| yes |none|

> Response Examples

> 200 Response

```json
{
  "code": "string",
  "message": "string"
}
```

### Responses

|HTTP Status Code |Meaning|Description|Data schema|
|---|---|---|---|
|200|[OK](https://tools.ietf.org/html/rfc7231#section-6.3.1)|none|Inline|
|400|[Bad Request](https://tools.ietf.org/html/rfc7231#section-6.5.1)|none|Inline|

### Responses Data Schema

HTTP Status Code **200**

|Name|Type|Required|Restrictions|Title|description|
|---|---|---|---|---|---|
|» code|string|true|none||none|
|» message|string|true|none||none|

HTTP Status Code **400**

|Name|Type|Required|Restrictions|Title|description|
|---|---|---|---|---|---|
|» status|string|true|none||none|
|» message|string|true|none||none|
|» error|object|true|none||none|

# Order Management API

## GET List Orders

GET /orders/

List all orders.

### Params

|Name|Location|Type|Required|Description|
|---|---|---|---|---|
|page|query|string| no |Page number for pagination.|
|limit|query|string| no |Limit the number of results per page.|

> Response Examples

> 200 Response

```json
{
  "code": "string",
  "message": "string",
  "data": [
    {
      "id": "string",
      "status": "string",
      "total_price": "string",
      "created_at": "string"
    }
  ],
  "pagination": [
    {
      "page": 0,
      "limit": 0,
      "total": 0
    }
  ]
}
```

### Responses

|HTTP Status Code |Meaning|Description|Data schema|
|---|---|---|---|
|200|[OK](https://tools.ietf.org/html/rfc7231#section-6.3.1)|none|Inline|
|400|[Bad Request](https://tools.ietf.org/html/rfc7231#section-6.5.1)|none|Inline|

### Responses Data Schema

HTTP Status Code **200**

|Name|Type|Required|Restrictions|Title|description|
|---|---|---|---|---|---|
|» code|string|true|none||none|
|» message|string|true|none||none|
|» data|[[order](#schemaorder)]|true|none||none|
|»» id|string|true|none||none|
|»» status|string|true|none||none|
|»» total_price|string|true|none||none|
|»» created_at|string|true|none||none|
|» pagination|[[pagination](#schemapagination)]|true|none||none|
|»» page|number|true|none||none|
|»» limit|number|true|none||none|
|»» total|number|true|none||none|

HTTP Status Code **400**

|Name|Type|Required|Restrictions|Title|description|
|---|---|---|---|---|---|
|» status|string|true|none||none|
|» message|string|true|none||none|
|» error|object|true|none||none|

## POST Create Order

POST /orders/

Create a new order from the user's cart.

> Body Parameters

```json
{
  "cart_id": "string",
  "address": "string",
  "payment_method": "string"
}
```

### Params

|Name|Location|Type|Required|Description|
|---|---|---|---|---|
|body|body|object| no |none|
|» cart_id|body|string| yes |none|
|» address|body|string| yes |none|
|» payment_method|body|string| yes |none|

> Response Examples

> 200 Response

```json
{
  "code": "string",
  "message": "string",
  "data": [
    {
      "id": "string",
      "status": "string",
      "total_price": "string",
      "created_at": "string"
    }
  ]
}
```

### Responses

|HTTP Status Code |Meaning|Description|Data schema|
|---|---|---|---|
|200|[OK](https://tools.ietf.org/html/rfc7231#section-6.3.1)|none|Inline|

### Responses Data Schema

HTTP Status Code **200**

|Name|Type|Required|Restrictions|Title|description|
|---|---|---|---|---|---|
|» code|string|true|none||none|
|» message|string|true|none||none|
|» data|[[order](#schemaorder)]|true|none||none|
|»» id|string|true|none||none|
|»» status|string|true|none||none|
|»» total_price|string|true|none||none|
|»» created_at|string|true|none||none|

## GET Retrieve Order

GET /orders/{id}

Retrieve a specific order by ID.

### Params

|Name|Location|Type|Required|Description|
|---|---|---|---|---|
|id|path|string| yes |The unique ID of the order.|

> Response Examples

> 200 Response

```json
{
  "code": "string",
  "message": "string",
  "data": [
    {
      "id": "string",
      "status": "string",
      "total_price": "string",
      "created_at": "string"
    }
  ]
}
```

### Responses

|HTTP Status Code |Meaning|Description|Data schema|
|---|---|---|---|
|200|[OK](https://tools.ietf.org/html/rfc7231#section-6.3.1)|none|Inline|
|400|[Bad Request](https://tools.ietf.org/html/rfc7231#section-6.5.1)|none|Inline|

### Responses Data Schema

HTTP Status Code **200**

|Name|Type|Required|Restrictions|Title|description|
|---|---|---|---|---|---|
|» code|string|true|none||none|
|» message|string|true|none||none|
|» data|[[order](#schemaorder)]|true|none||none|
|»» id|string|true|none||none|
|»» status|string|true|none||none|
|»» total_price|string|true|none||none|
|»» created_at|string|true|none||none|

HTTP Status Code **400**

|Name|Type|Required|Restrictions|Title|description|
|---|---|---|---|---|---|
|» status|string|true|none||none|
|» message|string|true|none||none|
|» error|object|true|none||none|

## DELETE Delete an order by ID

DELETE /orders/{id}

Delete an order by ID.

### Params

|Name|Location|Type|Required|Description|
|---|---|---|---|---|
|id|path|string| yes |none|

> Response Examples

> 204 Response

```json
{
  "code": "string",
  "message": "string",
  "data": {}
}
```

### Responses

|HTTP Status Code |Meaning|Description|Data schema|
|---|---|---|---|
|204|[No Content](https://tools.ietf.org/html/rfc7231#section-6.3.5)|none|Inline|
|400|[Bad Request](https://tools.ietf.org/html/rfc7231#section-6.5.1)|none|Inline|

### Responses Data Schema

HTTP Status Code **204**

|Name|Type|Required|Restrictions|Title|description|
|---|---|---|---|---|---|
|» code|string|true|none||none|
|» message|string|true|none||none|
|» data|object|true|none||none|

HTTP Status Code **400**

|Name|Type|Required|Restrictions|Title|description|
|---|---|---|---|---|---|
|» status|string|true|none||none|
|» message|string|true|none||none|
|» error|object|true|none||none|

# Prescription management API

## GET get all prescriptions

GET /prescriptions

Get all prescriptions

> Response Examples

```json
{
  "code": "81",
  "message": "Tero adinventitias blanditiis taceo. Aperiam caelestis vulgivagus vero tempora casus. Vergo addo impedit abeo conturbo virgo defero aequus.",
  "data": {}
}
```

### Responses

|HTTP Status Code |Meaning|Description|Data schema|
|---|---|---|---|
|200|[OK](https://tools.ietf.org/html/rfc7231#section-6.3.1)|none|Inline|

### Responses Data Schema

HTTP Status Code **200**

|Name|Type|Required|Restrictions|Title|description|
|---|---|---|---|---|---|
|» code|string|true|none||none|
|» message|string|true|none||none|
|» data|[[prescription](#schemaprescription)]|true|none||none|
|»» id|string|true|none||none|
|»» username|string|true|none||none|
|»» image|string|true|none||none|
|»» *anonymous*|string|false|none||none|

## POST Create prescription

POST /prescriptions

Create a new prescription

> Body Parameters

```yaml
image: file://C:\Users\semre\Documents\A-sample-prescription-image-in-grayscale-version.png

```

### Params

|Name|Location|Type|Required|Description|
|---|---|---|---|---|
|body|body|object| no |none|
|» image|body|string(binary)| no |Patient's prescription image|

> Response Examples

```json
{
  "code": "68",
  "message": "Suppellex verumtamen cauda sumo cura dicta tergiversatio decens. Adicio similique et. Toties commodo aggredior aptus undique.",
  "data": {}
}
```

### Responses

|HTTP Status Code |Meaning|Description|Data schema|
|---|---|---|---|
|201|[Created](https://tools.ietf.org/html/rfc7231#section-6.3.2)|none|Inline|

### Responses Data Schema

HTTP Status Code **201**

|Name|Type|Required|Restrictions|Title|description|
|---|---|---|---|---|---|
|» code|string|true|none||none|
|» message|string|true|none||none|
|» data|[[prescription](#schemaprescription)]|true|none||none|
|»» id|string|true|none||none|
|»» username|string|true|none||none|
|»» image|string|true|none||none|
|»» *anonymous*|string|false|none||none|

## GET Get prescription by ID

GET /prescriptions/{id}

Get prescription by ID

### Params

|Name|Location|Type|Required|Description|
|---|---|---|---|---|
|id|path|string| yes |none|
|id|query|string| yes |none|

> Response Examples

```json
{
  "code": "55",
  "message": "Ancilla adsuesco clamo patria vicinus. Clamo dolore cum desidero. Pax centum thesaurus usque spiculum comprehendo.",
  "data": {}
}
```

### Responses

|HTTP Status Code |Meaning|Description|Data schema|
|---|---|---|---|
|200|[OK](https://tools.ietf.org/html/rfc7231#section-6.3.1)|none|Inline|

### Responses Data Schema

HTTP Status Code **200**

|Name|Type|Required|Restrictions|Title|description|
|---|---|---|---|---|---|
|» code|string|true|none||none|
|» message|string|true|none||none|
|» data|[[prescription](#schemaprescription)]|true|none||none|
|»» id|string|true|none||none|
|»» username|string|true|none||none|
|»» image|string|true|none||none|
|»» *anonymous*|string|false|none||none|

## PUT Update prescription

PUT /prescription

Update a prescription

> Body Parameters

```yaml
image: file://C:\Users\semre\Documents\A-sample-prescription-image-in-grayscale-version.png

```

### Params

|Name|Location|Type|Required|Description|
|---|---|---|---|---|
|username|query|string| yes |none|
|body|body|object| no |none|
|» image|body|string(binary)| no |none|

> Response Examples

```json
{
  "code": "75",
  "message": "Viriliter curtus ventito utrum peccatus suffragium angustus. Quis approbo caput vero aeternus audentia vicinus curso degusto minus. Complectus speculum aspicio uter vomer.",
  "data": {}
}
```

### Responses

|HTTP Status Code |Meaning|Description|Data schema|
|---|---|---|---|
|200|[OK](https://tools.ietf.org/html/rfc7231#section-6.3.1)|none|Inline|

### Responses Data Schema

HTTP Status Code **200**

|Name|Type|Required|Restrictions|Title|description|
|---|---|---|---|---|---|
|» code|string|true|none||none|
|» message|string|true|none||none|
|» data|[[prescription](#schemaprescription)]|true|none||none|
|»» id|string|true|none||none|
|»» username|string|true|none||none|
|»» image|string|true|none||none|
|»» *anonymous*|string|false|none||none|

## DELETE Delete prescription

DELETE /prescription

### Params

|Name|Location|Type|Required|Description|
|---|---|---|---|---|
|id|query|string| yes |ID|

> Response Examples

> 204 Response

```json
{
  "code": "string",
  "message": "string",
  "data": {}
}
```

### Responses

|HTTP Status Code |Meaning|Description|Data schema|
|---|---|---|---|
|204|[No Content](https://tools.ietf.org/html/rfc7231#section-6.3.5)|none|Inline|

### Responses Data Schema

HTTP Status Code **204**

|Name|Type|Required|Restrictions|Title|description|
|---|---|---|---|---|---|
|» code|string|true|none||none|
|» message|string|true|none||none|
|» data|object|true|none||none|

# Data Schema

<h2 id="tocS_Pharmacist">Pharmacist</h2>

<a id="schemapharmacist"></a>
<a id="schema_Pharmacist"></a>
<a id="tocSpharmacist"></a>
<a id="tocspharmacist"></a>

```json
{
  "id": "string",
  "name": "string",
  "email": "string",
  "contact": "string"
}

```

### Attribute

|Name|Type|Required|Restrictions|Title|Description|
|---|---|---|---|---|---|
|id|string|true|none||ID|
|name|string|true|none||none|
|email|string|true|none||none|
|contact|string|true|none||none|

<h2 id="tocS_Patient">Patient</h2>

<a id="schemapatient"></a>
<a id="schema_Patient"></a>
<a id="tocSpatient"></a>
<a id="tocspatient"></a>

```json
{
  "id": "string",
  "username": "string",
  "email": "string"
}

```

### Attribute

|Name|Type|Required|Restrictions|Title|Description|
|---|---|---|---|---|---|
|id|string|true|none||none|
|username|string|true|none||none|
|email|string|true|none||none|

<h2 id="tocS_Drug">Drug</h2>

<a id="schemadrug"></a>
<a id="schema_Drug"></a>
<a id="tocSdrug"></a>
<a id="tocsdrug"></a>

```json
{
  "id": "string",
  "name": "string",
  "description": "string",
  "price": 0
}

```

### Attribute

|Name|Type|Required|Restrictions|Title|Description|
|---|---|---|---|---|---|
|id|string|true|none||none|
|name|string|true|none||none|
|description|string|true|none||none|
|price|integer|true|none||none|

<h2 id="tocS_items">items</h2>

<a id="schemaitems"></a>
<a id="schema_items"></a>
<a id="tocSitems"></a>
<a id="tocsitems"></a>

```json
{
  "item_id": "string",
  "drug_id": "string",
  "name": "string",
  "quantity": 0,
  "price": 0
}

```

### Attribute

|Name|Type|Required|Restrictions|Title|Description|
|---|---|---|---|---|---|
|item_id|string|true|none||none|
|drug_id|string|true|none||none|
|name|string|true|none||name|
|quantity|number|true|none||none|
|price|integer|true|none||none|

<h2 id="tocS_order">order</h2>

<a id="schemaorder"></a>
<a id="schema_order"></a>
<a id="tocSorder"></a>
<a id="tocsorder"></a>

```json
{
  "id": "string",
  "status": "string",
  "total_price": "string",
  "created_at": "string"
}

```

### Attribute

|Name|Type|Required|Restrictions|Title|Description|
|---|---|---|---|---|---|
|id|string|true|none||none|
|status|string|true|none||none|
|total_price|string|true|none||none|
|created_at|string|true|none||none|

<h2 id="tocS_pagination">pagination</h2>

<a id="schemapagination"></a>
<a id="schema_pagination"></a>
<a id="tocSpagination"></a>
<a id="tocspagination"></a>

```json
{
  "page": 0,
  "limit": 0,
  "total": 0
}

```

### Attribute

|Name|Type|Required|Restrictions|Title|Description|
|---|---|---|---|---|---|
|page|number|true|none||none|
|limit|number|true|none||none|
|total|number|true|none||none|

<h2 id="tocS_prescription">prescription</h2>

<a id="schemaprescription"></a>
<a id="schema_prescription"></a>
<a id="tocSprescription"></a>
<a id="tocsprescription"></a>

```json
{
  "id": "string",
  "username": "string",
  "image": "string",
  "": "string"
}

```

### Attribute

|Name|Type|Required|Restrictions|Title|Description|
|---|---|---|---|---|---|
|id|string|true|none||none|
|username|string|true|none||none|
|image|string|true|none||none|
|*anonymous*|string|false|none||none|

