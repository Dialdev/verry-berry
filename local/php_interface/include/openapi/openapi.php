<?php
/**
 * @OA\OpenApi(
 *     @OA\Info(
 *         version="1.0",
 *         title="Api very-berry.ru"
 *     ),
 *     @OA\Server(
 *         description="Api server demo",
 *         url="http://berry.xx28.ru"
 *     ),
 *
 *     @OA\Server(
 *         description="Api server demo artem",
 *         url="http://artem.berry.xx28.ru"
 *     ),
 *
 *     security={
 *         {"api_key": {}}
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="response_success",
 *     description="",
 *     title="Успешный ответ",
 *     type="object",
 *     properties={
 *         @OA\Property(property="success", type="boolean", example=true, description="всегда true, если код ответа 200"),
 *         @OA\Property(property="meta", type="object",
 *             @OA\Property(property="service", type="object",
 *                 @OA\Property(property="name", type="string", example="very-berry"),
 *             )
 *        )
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="response_error_v2",
 *     description="",
 *     title="Ответ с ошибкой",
 *     type="object",
 *     properties={
 *         @OA\Property(property="success", type="boolean", example=false, description="всегда false, если код ответа не 200"),
 *         @OA\Property(property="meta", type="object",
 *             @OA\Property(property="service", type="object",
 *                 @OA\Property(property="name", type="string", example="very-berry"),
 *             )
 *        )
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="image",
 *     description="",
 *     title="Картинка",
 *     type="object",
 *     properties={
 *         @OA\Property(property="id", type="integer", example="79", description="ИД картинки"),
 *         @OA\Property(property="src", type="string", example="/upload/iblock/ac1/ac1e8ff6f3afe5e3891503ab9859689a.jpg", description="ссылка на оригинальную картинку"),
 *         @OA\Property(property="small_src", type="string", example="/upload/resize_cache/iblock/ac1/131_98_1/ac1e8ff6f3afe5e3891503ab9859689a.jpg", description="ссылка на уменьшенную картинку"),
 *         @OA\Property(property="is_preview", type="bool", example=true, description="является картинкой товара"),
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="price",
 *     description="",
 *     title="Цена",
 *     type="object",
 *     properties={
 *         @OA\Property(property="price", type="float", example="980", description="базовая цена"),
 *         @OA\Property(property="price_discount", type="float", example="882", description="цена со скидкой"),
 *         @OA\Property(property="price_format", type="string", example="980 ₽", description="отформатированная базовая цена"),
 *         @OA\Property(property="price_discount_format", type="string", example="882 ₽", description="отформатированная цена со скидкой"),
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="size",
 *     description="",
 *     title="Размер",
 *     type="object",
 *     properties={
 *         @OA\Property(property="id", type="integer", example=1, description="идентификатор размера комплекта-букета"),
 *         @OA\Property(property="name", type="string", example="S", description="название размера комплекта-букета"),
 *     },
 * )
 *
 * @OA\Schema(
 *     schema="set_bouquet",
 *     description="",
 *     title="Данные комплекта-букета",
 *     type="object",
 *     properties={
 *         @OA\Property(property="id", type="integer", example="55", description="ИД комплекта-букета"),
 *         @OA\Property(property="name", type="string", example="Букет с клубникой, размер S, Г, черный", description="Название комплекта-букета"),
 *         @OA\Property(property="card_name", type="string", example="Букеты с клубникой", description="Альтернативное название комплекта-букета"),
 *         @OA\Property(property="code", type="string", example="buket-s-klubnikoy-razmer-s-g-chernyy", description="Символьный код комплекта-букета"),
 *         @OA\Property(property="url", type="string", example="/product/buket-s-klubnikoy-razmer-s-g-chernyy/", description="Ссылка на карточку товара"),
 *         @OA\Property(property="image", type="object", ref="#/components/schemas/image"),
 *         @OA\Property(property="available", type="bool", example=true, description="Флаг наличия комплекта-букета"),
 *         @OA\Property(property="price", ref="#/components/schemas/price"),
 *         @OA\Property(property="size", ref="#/components/schemas/size"),
 *         @OA\Property(
 *             property="bouquet",
 *             type="object",
 *             properties={
 *                 @OA\Property(property="id", type="integer", example=28, description="идентификатор основы букета"),
 *                 @OA\Property(property="name", type="string", example="Букет с клубникой [S]", description="название основы букета"),
 *                 @OA\Property(property="code", type="string", example="buket-s-klubnikoy-s", description="символьный код основы букета"),
 *                 @OA\Property(property="image", type="object", ref="#/components/schemas/image"),
 *                 @OA\Property(property="available", type="bool", example=true, description="Флаг наличия основы букета"),
 *                 @OA\Property(property="price", ref="#/components/schemas/price"),
 *                 @OA\Property(property="size", ref="#/components/schemas/size"),
 *             }
 *         ),
 *         @OA\Property(
 *             property="berries",
 *             type="array",
 *             @OA\Items(
 *                 description="",
 *                 title="Дополнительные ягоды комплекта-букета",
 *                 properties={
 *                     @OA\Property(property="id", type="integer", example=13, description="идентификатор дополнительной ягоды"),
 *                     @OA\Property(property="name", type="string", example="Голубика [S]", description="название дополнительной ягоды"),
 *                     @OA\Property(property="card_name", type="string", example="Голубика", description="альтернативное название дополнительной ягоды"),
 *                     @OA\Property(property="size", ref="#/components/schemas/size"),
 *                     @OA\Property(property="image", type="object", ref="#/components/schemas/image"),
 *                     @OA\Property(property="available", type="bool", example=true, description="Флаг наличия дополнительной ягоды"),
 *                     @OA\Property(property="price", ref="#/components/schemas/price"),
 *                 }
 *             )
 *         ),
 *         @OA\Property(
 *             property="packing",
 *             type="object",
 *             properties={
 *                 @OA\Property(property="id", type="integer", example=5, description="идентификатор упаковки"),
 *                 @OA\Property(property="name", type="string", example="Чёрная упаковка", description="название упаковки"),
 *                 @OA\Property(property="card_name", type="string", example="Чёрная", description="альтернативное название упаковки"),
 *                 @OA\Property(property="image", type="object", ref="#/components/schemas/image"),
 *                 @OA\Property(property="available", type="bool", example=true, description="Флаг наличия упаковки"),
 *             }
 *         ),
 *         @OA\Property(
 *             property="dop_images",
 *             type="array",
 *             @OA\Items(
 *                 description="",
 *                 title="Дополнительные картинки букета-комплекта",
 *                 @OA\Property(property="image", type="object", ref="#/components/schemas/image"),
 *             )
 *         ),
 *         @OA\Property(property="articul", type="string", example="100001", description="артикул комплекта-букета"),
 *     }
 * )
 */
