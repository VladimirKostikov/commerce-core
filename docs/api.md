# REST API

База JSON: http://localhost/api. Заголовок тела: Content-Type: application/json. Ответы JSON, кроме корня, Laravel /up и прогона проверок: там текст.

Ключ Sanctum: Authorization: Bearer {token}. Нужен для logout, me и заказов. Остальные пути без ключа.

После сидов: test@example.com / password. Тот же контракт в README, раздел API.

## Коды

| Код | Когда |
| --- | --- |
| 200 | успех |
| 201 | создан пользователь или заказ |
| 401 | нет ключа или неверный пароль |
| 404 | нет товара, заказа или поставщика |
| 422 | валидация или товар выключен |
| 503 | проверка живости не прошла или заглушка в режиме отказа |

Ошибка валидации:

```json
{
  "message": "The email field is required. (and 1 more error)",
  "errors": {
    "email": ["The email field is required."]
  }
}
```

401: { "message": "Unauthenticated." }. Неверный пароль: Invalid credentials. Нет заказа: Order not found. Нет товара: Product not found.

Статус заказа: created, paid, delivering, delivered, payment_failed, out_of_stock, delivery_failed. Валюта: RUB. Тип товара: topup, key, subscription, giftcard.

## Маршруты

| Метод | Путь | Ключ | Тип ответа |
| --- | --- | --- | --- |
| GET | / | нет | text/plain |
| GET | /up | нет | text/html |
| GET | /api/health | нет | application/json |
| GET | /api/catalog | нет | application/json |
| GET | /api/catalog/{sku} | нет | application/json |
| POST | /api/register | нет | application/json |
| POST | /api/login | нет | application/json |
| POST | /api/logout | да | application/json |
| GET | /api/me | да | application/json |
| POST | /api/orders | да | application/json |
| GET | /api/orders/{id} | да | application/json |
| POST | /api/webhook/payment | нет | application/json |
| GET | /api/reconciliation | нет | application/json |
| POST | /api/stub/suppliers/{a\|b}/issue | нет | application/json |
| GET, POST | /api/tests/run | нет | text/plain |
| GET | /api/tests/log | нет | text/plain |

## GET /

Живость процесса. Тело нет.

```bash
curl -s http://localhost/
```

200, текст: Application is running.

## GET /up

Проверка Laravel. Тело нет.

```bash
curl -s -o /dev/null -w '%{http_code}' http://localhost/up
```

200, если приложение поднято.

## GET /api/health

Проверки postgres, redis, rabbitmq, kafka, clickhouse. Тело нет.

```bash
curl -s http://localhost/api/health
```

200, если все живы, иначе 503.

| Поле | Тип |
| --- | --- |
| status | string: ok или error |
| checks | array |
| checks[].name | string |
| checks[].ok | boolean |
| checks[].message | string или null |

```json
{
  "status": "ok",
  "checks": [
    { "name": "postgres", "ok": true, "message": null },
    { "name": "redis", "ok": true, "message": null },
    { "name": "rabbitmq", "ok": true, "message": null },
    { "name": "kafka", "ok": true, "message": null },
    { "name": "clickhouse", "ok": true, "message": null }
  ]
}
```

## GET /api/catalog

Товары в наличии. Сортировка: остаток по убыванию, затем sku. Нулевой остаток в список не входит.

| Параметр | Где | Тип | Обязателен | Ограничение |
| --- | --- | --- | --- | --- |
| limit | query | integer | нет | 1..100, по умолчанию 50 |

```bash
curl -s 'http://localhost/api/catalog?limit=10'
```

200.

| Поле | Тип |
| --- | --- |
| items | array |
| items[].sku | string |
| items[].name | string |
| items[].price | integer |
| items[].currency | string |
| items[].type | string |
| items[].available_keys_count | integer |
| items[].image | string или null |

```json
{
  "items": [
    {
      "sku": "STEAM-TOPUP-500",
      "name": "Steam 500",
      "price": 500,
      "currency": "RUB",
      "type": "topup",
      "available_keys_count": 12,
      "image": null
    }
  ]
}
```

limit=0 или limit=101: 422.

## GET /api/catalog/{sku}

Один активный товар, в том числе с нулевым остатком.

| Параметр | Где | Тип | Обязателен |
| --- | --- | --- | --- |
| sku | path | string | да |

```bash
curl -s http://localhost/api/catalog/STEAM-TOPUP-500
```

200: объект товара без обёртки items. Нет артикула: 404. Товар выключен: 422.

## POST /api/register

Создаёт пользователя и выдаёт ключ.

| Поле | Где | Тип | Обязателен | Ограничение |
| --- | --- | --- | --- | --- |
| name | body | string | да | max 255 |
| email | body | string | да | email, уникален, нижний регистр |
| password | body | string | да | min 8 |
| password_confirmation | body | string | да | совпадает с password |

```bash
curl -s -X POST http://localhost/api/register \
  -H 'Content-Type: application/json' \
  -d '{"name":"Ada","email":"ada@example.com","password":"password1","password_confirmation":"password1"}'
```

201.

| Поле | Тип |
| --- | --- |
| token | string |
| token_type | string, всегда Bearer |
| user | object |
| user.id | integer |
| user.name | string |
| user.email | string |

```json
{
  "token": "1|abcdefghijklmnopqrstuvwxyz",
  "token_type": "Bearer",
  "user": { "id": 1, "name": "Ada", "email": "ada@example.com" }
}
```

## POST /api/login

| Поле | Где | Тип | Обязателен |
| --- | --- | --- | --- |
| email | body | string | да |
| password | body | string | да |

```bash
curl -s -X POST http://localhost/api/login \
  -H 'Content-Type: application/json' \
  -d '{"email":"test@example.com","password":"password"}'
```

200, тот же объект, что у регистрации. 401 при неверных данных.

## POST /api/logout

Отзывает текущий ключ. Тело нет.

```bash
curl -s -X POST http://localhost/api/logout \
  -H 'Authorization: Bearer 1|abcdefghijklmnopqrstuvwxyz'
```

200: { "ok": true }. Поле ok: boolean. Без ключа: 401.

## GET /api/me

Тело нет.

```bash
curl -s http://localhost/api/me \
  -H 'Authorization: Bearer 1|abcdefghijklmnopqrstuvwxyz'
```

200.

| Поле | Тип |
| --- | --- |
| id | integer |
| name | string |
| email | string |

```json
{ "id": 1, "name": "Ada", "email": "test@example.com" }
```

## POST /api/orders

| Поле | Где | Тип | Обязателен | Ограничение |
| --- | --- | --- | --- | --- |
| sku | body | string | да | max 64 |

```bash
curl -s -X POST http://localhost/api/orders \
  -H 'Authorization: Bearer 1|abcdefghijklmnopqrstuvwxyz' \
  -H 'Content-Type: application/json' \
  -d '{"sku":"STEAM-TOPUP-500"}'
```

201. Идентификатор начинается с ord_. Сумма копируется с цены товара.

| Поле | Тип |
| --- | --- |
| id | string |
| sku | string |
| amount | integer |
| currency | string |
| status | string |
| delivery_code | string или null |

```json
{
  "id": "ord_01hxyz",
  "sku": "STEAM-TOPUP-500",
  "amount": 500,
  "currency": "RUB",
  "status": "created",
  "delivery_code": null
}
```

401 без ключа. 404 неизвестный артикул. 422 выключенный товар.

## GET /api/orders/{id}

| Параметр | Где | Тип | Обязателен |
| --- | --- | --- | --- |
| id | path | string | да |

```bash
curl -s http://localhost/api/orders/ord_01hxyz \
  -H 'Authorization: Bearer 1|abcdefghijklmnopqrstuvwxyz'
```

200, тот же объект заказа. После удачной выдачи delivery_code заполнен. Чужой или неизвестный заказ: 404.

## POST /api/webhook/payment

Без ключа. Повтор того же event_id: 200, заказ не меняется.

| Поле | Где | Тип | Обязателен | Ограничение |
| --- | --- | --- | --- | --- |
| event_id | body | string | да | max 64 |
| order_id | body | string | да | max 40 |
| status | body | string | да | paid или failed |
| amount | body | integer | да | min 0 |
| currency | body | string | да | RUB |
| created_at | body | string | да | дата ISO 8601 |

```bash
curl -s -X POST http://localhost/api/webhook/payment \
  -H 'Content-Type: application/json' \
  -d '{"event_id":"evt_1","order_id":"ord_01hxyz","status":"paid","amount":500,"currency":"RUB","created_at":"2026-08-31T12:00:00Z"}'
```

200: { "accepted": true }. Поле accepted: boolean.

Нет заказа: событие принимается, заказ не трогается. paid запускает выдачу. failed ставит payment_failed, кода нет. Пустое тело: 422.

## GET /api/reconciliation

Расхождения и журнал. Тело нет.

```bash
curl -s http://localhost/api/reconciliation
```

200.

| Поле | Тип |
| --- | --- |
| paid_not_delivered | array |
| paid_not_delivered[].id | string |
| paid_not_delivered[].status | string |
| paid_not_delivered[].amount | integer |
| paid_not_delivered[].delivery_code | string или null |
| delivered_not_paid | array, тот же объект |
| ledger | object |
| ledger.debit | integer |
| ledger.credit | integer |
| ledger.balanced | boolean |

```json
{
  "paid_not_delivered": [
    { "id": "ord_01hxyz", "status": "paid", "amount": 500, "delivery_code": null }
  ],
  "delivered_not_paid": [],
  "ledger": { "debit": 1000, "credit": 1000, "balanced": true }
}
```

## POST /api/stub/suppliers/{supplier}/issue

Заглушка поставщика. Тот же request_id возвращает тот же код.

| Параметр | Где | Тип | Обязателен | Ограничение |
| --- | --- | --- | --- | --- |
| supplier | path | string | да | a или b |
| request_id | body | string | да | max 64 |
| sku | body | string | да | max 64 |
| order_id | body | string | да | max 40 |

```bash
curl -s -X POST http://localhost/api/stub/suppliers/a/issue \
  -H 'Content-Type: application/json' \
  -d '{"request_id":"req_1","sku":"STEAM-TOPUP-500","order_id":"ord_01hxyz"}'
```

200 при успехе.

| Поле | Тип |
| --- | --- |
| status | string: ok или error |
| request_id | string, только при успехе |
| code | string, только при успехе |
| reason | string, только при ошибке |

```json
{ "status": "ok", "request_id": "req_1", "code": "LFXC-TNCS-BPCD" }
```

Нет на складе: 200, { "status": "error", "reason": "out_of_stock" }. Неизвестный поставщик: 404, unknown_supplier. Режим отказа: 503, unavailable. Пустое тело: 422.

## GET /api/tests/run и POST /api/tests/run

Служебный прогон PHPUnit. Ответ текст.

| Поле | Где | Тип | Обязателен | Ограничение |
| --- | --- | --- | --- | --- |
| suite | query или body | string | да, если нет case/class | all, Unit, Component, Feature, Api, Functional, Database, Integration, System, E2E, Performance |
| case | query или body | string | да, если нет suite/class | class::method из каталога |
| class | query или body | string | вместе с method | FQCN теста |
| method | query или body | string | вместе с class | имя метода |

```bash
curl -s 'http://localhost/api/tests/run?suite=Unit'
curl -s 'http://localhost/api/tests/run?class=Tests%5CSystem%5CExactlyOnceDeliveryTest&method=test_fifty_parallel_paid_webhooks_deliver_once'
curl -s -X POST http://localhost/api/tests/run \
  -H 'Content-Type: application/json' \
  -d '{"suite":"System"}'
```

200, Content-Type: text/plain; charset=UTF-8. Первая строка: Unit  ok или Unit  fail, дальше вывод PHPUnit. Неизвестный набор: 422.

## GET /api/tests/log

Текст последнего прогона кейса.

| Поле | Где | Тип | Обязателен |
| --- | --- | --- | --- |
| case | query | string | да, если нет class |
| class | query | string | вместе с method |
| method | query | string | вместе с class |

```bash
curl -s 'http://localhost/api/tests/log?case=Tests%5CSystem%5CExactlyOnceDeliveryTest%3A%3Atest_fifty_parallel_paid_webhooks_deliver_once'
```

200, текст лога. Нет записи: пусто.

## Контур после сидов

```bash
TOKEN=$(curl -s -X POST http://localhost/api/login \
  -H 'Content-Type: application/json' \
  -d '{"email":"test@example.com","password":"password"}' | jq -r .token)

ORDER=$(curl -s -X POST http://localhost/api/orders \
  -H "Authorization: Bearer $TOKEN" \
  -H 'Content-Type: application/json' \
  -d '{"sku":"STEAM-TOPUP-500"}')

curl -s -X POST http://localhost/api/webhook/payment \
  -H 'Content-Type: application/json' \
  -d '{"event_id":"evt_1","order_id":"ord_...","status":"paid","amount":500,"currency":"RUB","created_at":"2026-08-31T12:00:00Z"}'
```

Из ответа заказа взять id. Сумма у STEAM-TOPUP-500 равна 500.
