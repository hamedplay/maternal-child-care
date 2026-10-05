<?php

/**
 * تنظیمات هوش مصنوعی مستقیم از OpenAI.
 * کلید باید متعلق به حساب/Project هاشمی باشد و فقط در Environment Variable نگهداری شود.
 */
return [
    'openai_api_key' => getenv('OPENAI_API_KEY') ?: '',
    'model' => getenv('OPENAI_MODEL') ?: 'gpt-4o-mini',
    'max_history_messages' => 12,
];
