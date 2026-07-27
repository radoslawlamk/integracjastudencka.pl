<main class="legal-page">
    <section>
        <p class="eyebrow">Zgody</p>
        <h1>Zarządzanie powiadomieniami</h1>
        <?php if (!$contact): ?>
            <p>Ten link jest nieaktywny albo kontakt został już usunięty z bazy.</p>
        <?php else: ?>
            <?php if ($status === 'ok'): ?>
                <p class="legal-success">Zapisaliśmy zmianę zgód.</p>
            <?php elseif ($status === 'blad'): ?>
                <p class="legal-error">Nie udało się zapisać zmiany. Spróbuj ponownie.</p>
            <?php endif; ?>
            <p>Możesz zostawić wybrany kanał komunikacji albo odznaczyć wszystko. Jeśli odznaczysz email i SMS, usuniemy kontakt z bazy marketingowej.</p>
            <form method="post" action="/rezygnacja/<?= e($token) ?>" class="consent-manage-form">
                <?= csrf_field() ?>
                <?= spam_trap_field() ?>
                <label><input type="checkbox" name="email_consent" value="1" <?= !empty($contact['email_consent']) ? 'checked' : '' ?>> Chcę otrzymywać powiadomienia email</label>
                <label><input type="checkbox" name="sms_consent" value="1" <?= !empty($contact['sms_consent']) ? 'checked' : '' ?>> Chcę otrzymywać powiadomienia SMS</label>
                <button class="btn primary">Zapisz moje zgody</button>
            </form>
        <?php endif; ?>
    </section>
</main>
