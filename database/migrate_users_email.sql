ALTER TABLE public.users
    ADD COLUMN email VARCHAR(255);

ALTER TABLE public.users
    ADD CONSTRAINT users_email_key UNIQUE (email);
