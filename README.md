# Laravel Ticket Reservation API

## 1. Çözüm Genel Bakışı
- Tüm bilet rezervasyon ve satın alma işlemleri **guest kullanıcılar** için yapılır.
- Fazla satış (**overselling**) hiçbir zaman oluşmaz.
- Rezervasyon süresi dolarsa bilet tekrar havuza eklenir.

**Mimari:**
- İş mantığı `ReservationService` içinde tutulur.
- Atomik DB işlemleri ve transaction’lar ile güvenli rezervasyon sağlanır.
- Rezervasyon süresi dolarsa `ExpireReservation` job çalışır.

---

## 2. Eşzamanlılık Stratejisi
- **Atomik decrement:** `available_tickets` alanı sadece >0 olduğunda düşer.
- **Transaction:** Rezervasyon ve bilet düşme işlemleri tek seferde yapılır.
- **Expire Job:** Süresi dolan rezervasyonlar biletleri geri havuza ekler.

---

## 3. Veritabanı Şeması
Detaylı şema için: [DrawSQL Ticket Reservation Diagram](https://drawsql.app/teams/eka-9/diagrams/ticket-reservation-api)

---

## 4. API Endpoint’leri
- Postman collection için: postman/Tickets.postman_collection.json dosyasını import edin.

---

## 5. Kurulum Talimatları
```bash
git clone <repo-url>
cd laravel-ticket-reservation
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan queue:work
```
## 6. Testleri Çalıştırma

```bash
php artisan test
```

