## Interactive flashcard

1. Clone repository. Go to cloned folder.
2. Copy `.env.example` to `.env`.
3. Run `sail up -d`.
4. Run `sail composer install`.
5. Run `sail artisan key:generate`.
6. Run `sail artisan migrate`.
7. Run `sail artisan flashcard:interactive`.
8. For test run `sail test`.

We can add multiple users by adding foreign key to table `practices`.