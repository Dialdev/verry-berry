<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("О нас");
?><style>
	.about{
		display: grid;
		grid-template-columns: 500px 1fr;
		gap: 90px;
		max-width: 1200px;
		margin: 0 auto;
		padding-bottom: 180px;
	}
	.about_left{
		position: relative;
	}
	.about_left img{
		border-radius: 10px;
	}
	.about_left::after{
		content: url('left2.png');
		display: block;
		max-width: 320px;
		-webkit-animation: spin 40s infinite linear;
		-moz-animation: spin 40s infinite linear;
		animation: spin 40s infinite linear;
		position: absolute;
		top: 70%;
		left: 65%;
	}
	@-moz-keyframes spin{
	  from{
	    -moz-transform: rotate(0deg);
	  }
	  to{
	    -moz-transform: rotate(360deg);
	  }
	}
	@-webkit-keyframes spin{
	  from{
	    -webkit-transform: rotate(0deg);
	  }
	  to{
	    -webkit-transform: rotate(360deg);
	  }
	}
	@keyframes spin{
	  from{
	    transform: rotate(0deg);
	  }
	  to{
	    transform: rotate(-360deg);
	  }
	}
	.about_right h2{
		font-family: 'Gilroy';
		font-style: normal;
		font-weight: 500;
		font-size: 40px;
		line-height: 49px;
		color: #D52A3F;
		margin: 0;
	}
	.about_right_before,
	.about_right_now{
		max-width: 440px;
	}
	.about_right_before{
		margin-top: 65px;
		margin-bottom: 80px;
	}
	.about_right_desc{
		margin-top: 10px;
		font-family: 'PT Root UI';
		font-style: normal;
		font-weight: 500;
		font-size: 18px;
		line-height: 140%;
		color: #8C909B;
	}
	.about_right_now{
		margin-left: 175px
	}
	.about_order{
		background-color: white;
		padding: 85px 0 100px;
	}
	.about_order h2{
		font-family: 'Gilroy';
		font-style: normal;
		font-weight: 600;
		font-size: 40px;
		line-height: 49px;
		max-width: 605px;
		margin: 0 0 60px;
	}
	.order_desc_grid{
		display: grid;
		grid-template-columns: 6.5fr 3.5fr;
		gap: 95px;
	}
	.left_order_grid{
		display: grid;
		grid-template-columns: 1fr 1fr 1fr;
		gap: 30px;
		align-items: center;
	}
	.order_time{
		font-family: 'Gilroy';
		font-style: normal;
		font-weight: 600;
		font-size: 30px;
		line-height: 37px;
		border-radius: 10px;
		display: flex;
		align-items: center;
		justify-content: center;
		height: 115px;
		position: relative;
	}
	.order_time::before {
		content: '';
		width: 9px;
		height: 9px;
		border-radius: 50%;
		display: block;
		position: absolute;
		left: 10px;
		top: 10px;
	}
	.order_time:nth-child(1){
		background-color: #FFF0F4;
		color: #FCB2C5;
	}
	.order_time:nth-child(2){
		background-color: #E6F4F2;
		color: #76C2B1;
	}
	.order_time:nth-child(3){
		background-color: #FFF4DB;
		color: #FFBF3F;
	}
	.order_time:nth-child(1)::before{
		background-color: #FCB2C5;
	}
	.order_time:nth-child(2)::before{
		background-color: #76C2B1;
	}
	.order_time:nth-child(3)::before{
		background-color: #FFBF3F;
	}
	.right_order_grid{
		font-family: 'PT Root UI';
		font-style: normal;
		font-weight: 500;
		font-size: 18px;
		line-height: 140%;
		color: #8C909B;
	}
	.right_order_grid span{
		color: #D52A3F;
	}
	.ingredients{
		max-width: 1200px;
		margin: 0 auto;
		display: grid;
		grid-template-columns: 1fr 1fr;
		gap: 85px;
		padding: 100px 0 120px;
	}
	.ingredients_left h2{
		margin-bottom: 60px;
		font-family: 'Gilroy';
		font-style: normal;
		font-weight: 600;
		font-size: 40px;
		line-height: 49px;
	}
	.ingredients_left p{
		font-family: 'PT Root UI';
		font-style: normal;
		font-weight: 500;
		font-size: 18px;
		line-height: 140%;
		color: #8C909B;
	}
	.ingredients_right{
		display: grid;
		grid-template-areas:
			'img_1 img_3'
			'img_2 img_3';
		gap: 35px;
	}
	.ingredients_right img{
		border-radius: 10px;
	}
	.ingredients_right img:nth-child(1){
		grid-area: img_1;
		margin-top: 100px;
	}
	.ingredients_right img:nth-child(2){
		grid-area: img_2;
	}
	.ingredients_right img:nth-child(3){
		grid-area: img_3;
	}
	.slogan{

	}
	.slogan h2{
		font-family: 'Gilroy';
		font-style: normal;
		font-weight: 600;
		font-size: 40px;
		line-height: 49px;
		text-align: center;
		color: #D52A3F;
		max-width: 904px;
		margin: 0 auto;
		padding-bottom: 90px;
	}
	.slogan{
		max-width: 1200px;
		margin: 0 auto;
	}
	.slogan p{
		font-family: 'PT Root UI';
		font-style: normal;
		font-weight: 500;
		font-size: 18px;
		line-height: 140%;
		color: #8C909B;
	}
	.slogan_center,
	.slogan_bottom{
		display: grid;
		grid-template-columns: 1fr 1fr;
		gap: 90px;
		padding-bottom: 100px;
	}
	.slogan_center_left .delivery{
		padding: 20px;
		background-color: white;
		border-radius: 10px;
	}
	.slogan_center_right img{
		border-radius: 10px;
	}
	.slogan_bottom h3{
		font-family: 'Gilroy';
		font-style: normal;
		font-weight: 600;
		font-size: 40px;
		line-height: 49px;
	}
	.slogan_bottom li{
		font-family: 'PT Root UI';
		font-style: normal;
		font-weight: 500;
		font-size: 18px;
		line-height: 140%;
		color: #8C909B;
		list-style-type: disc;
	}
	.slogan_bottom li::marker{
		color: #D52A3F;
	}
	.loyalty{
		background-color: white;
		padding: 85px 0;
	}
	.loyalty h2{
		font-family: 'Gilroy';
		font-style: normal;
		font-weight: 600;
		font-size: 40px;
		line-height: 49px;
		margin-bottom: 40px;
	}
	.loyalty p{
		color: #8C909B;
		font-family: 'PT Root UI';
		font-style: normal;
		font-weight: 500;
		font-size: 18px;
		line-height: 140%;
	}
	.loyalty span{
		color: #D52A3F;
	}
	.thank{
		color: #D52A3F;
		font-family: 'PT Root UI';
		font-style: normal;
		font-weight: 500;
		font-size: 20px;
		line-height: 140%;
		text-align: center;
		padding: 70px 0;
	}
	.thank p{
		display: grid;
		grid-template-columns: 1fr;
		margin: 0 auto;
		max-width: max-content;
	}
	.thank p::before{
		content: '';
		display: block;
		width: 21px;
		height: 18px;
		background-image: url('heart.png');
		background-repeat: no-repeat;
		margin: 0 auto;
	}
	@media screen and (max-width: 1200px){
		.about,
		.ingredients,
		.slogan,
		.thank{
			padding-left: 20px;
			padding-right: 20px;
		}
	}
	@media screen and (max-width: 768px){
		.about,
		.order_desc_grid,
		.ingredients,
		.slogan_center,
		.slogan_bottom{
			grid-template-columns: 1fr;
		}
		.about_left::after{
			left: 40%;
		}
		.slogan_bottom ul{
			padding: 0 20px;
		}
	}
	@media screen and (max-width: 551px){
		.about{
			gap: 0;
			padding-bottom: 60px;
		}
		.about_left{
			overflow: hidden;
		}
		.about_left::after{
			top: -20%;
			left: 65%;
		}
		.about_right_before{
			margin-top: 25px;
			margin-bottom: 40px;
		}
		.about_right_now{
			margin-left: 0;
		}
		.ingredients_right{
			grid-template-areas:
				'img_1 img_2'
				'img_3 img_3';
		}
		.ingredients_right img:nth-child(1){
			margin-top: 0;
		}
		.left_order_grid{
			gap: 10px;
		}
		.order_time{
			font-size: 16px;
		}
		.ingredients_left h2,
		.about_order h2,
		.slogan h2,
		.slogan_bottom h3,
		.loyalty h2{
			font-size: 24px;
			line-height: 29px;
		}
	}
</style>
<div class="container">
	<h1>О нас</h1>
</div>
<section class="about">
	<div class="about_left">
		<img src="left1.png" alt="">
	</div>
	<div class="about_right">
		<div class="about_right_before">
			<h2>c 2013 года</h2>
			<div class="about_right_desc">
				мы ежедневно собираем сочные клубничные сладкие подарки, которые покрываем премиальным бельгийским шоколадом Barry Callebaut и доставляем их по Москве и Московской области.
			</div>
		</div>
		<div class="about_right_now">
			<h2>в 2020 году</h2>
			<div class="about_right_desc">
				мы открыли второе производство в городе Санкт-Петербург и теперь в двух городах и областях можно порадовать своих родных и близких свежими и оригинальными подарками из ягод! Все композиции готовятся под заказ, что обеспечивает максимальную свежесть каждого букета.
			</div>
		</div>
	</div>
</section>
<section class="about_order">
	<div class="container">
		<h2>Мы выполняем заказы, оформленные заранее</h2>
		<div class="order_desc_grid">
			<div class="left_order_grid">
				<div class="order_time">
					За 1 день
				</div>
				<div class="order_time">
					За неделю
				</div>
				<div class="order_time">
					За месяц
				</div>
			</div>
			<div class="right_order_grid">
				а также есть возможность <span>срочной доставки день в день ориентировочно за 2,5 часа.</span> В это время закладывается сборка композиции с нуля и среднее время доставки в пределах города. 
			</div>
		</div>
	</div>
</section>
<section class="ingredients">
	<div class="ingredients_left">
		<h2>О наших ингредиентах</h2>
		<p>Для того, чтобы собрать наши прекрасные композиции мы используем только самые лучшие ингредиенты, а это: свежие ягоды каждый день, сезон для нас круглогодичный, так как ягоды мы покупаем у поставщиков из разных стран напрямую (в какой стране сезон на данный момент, оттуда и привозим), помимо клубники в наши букеты мы добавляем сочные спелые ягоды такие как малина, голубика, ежевика, физалис, черника.</p>
		<p>Шоколад используем только премиального бренда Barry Callebaut, не используем глазурь, так как ценим наших покупателей и хотим обрадовать каждого! Шоколад есть нескольких видов: молочный (самый популярный), темный, белый, розовый и голубой.  В качестве украшений у нас огромный ассортимент кондитерских посыпок. Самые популярные это кокосовая стружка, вафельная стружка, карамельный рис, арахис, сахарные шарики или сердечки, сублимированные ягоды.</p>
	</div>
	<div class="ingredients_right">
		<img src="Ingredients1.png" alt="">
		<img src="Ingredients2.png" alt="">
		<img src="Ingredients3.png" alt="">
	</div>
</section>
<section class="slogan">
	<h2>VeryBerryLab - это в первую очередь качество и любовь к каждой собранной нами композиции!</h2>
	<div class="slogan_center">
		<div class="slogan_center_left">
			<p>Наша продукция имеет уникальный дизайн, который разрабатывался годами нашими клиентами и флористами.</p>
			<p>Все наши букеты создаются для того, чтобы радовать заказчиков и получателей и дарить самые лучшие эмоции.</p>
			<p class="delivery">Доставка наших композиций будет осуществлена точно в срок, наши менеджеры ответят на любые ваши вопросы круглосуточно без выходных.</p>
		</div>
		<div class="slogan_center_right">
			<img src="slogan.png" alt="">
		</div>
	</div>
	<div class="slogan_bottom">
		<h3>Коллекция наших композиций - это более 1000 разновидностей</h3>
		<ul>
			<li>ягодных букетов</li>
			<li>шляпных коробок</li>
			<li>необычайных корзин</li>
			<li>подарочных наборов</li>
			<li>а также невероятных необычных шкатулок с выдвижным ящиком, где сверху представлены цветы, а в коробочке внизу ягоды в шоколаде. </li>
		</ul>
	</div>
</section>
<section class="loyalty">
	<div class="container">
		<h2>О программе лояльности</h2>
		<p>Помимо качественных ингредиентов, опрятных курьеров, доброжелательных менеджеров, оформляя заказ у нас на сайте вы так же автоматически становитесь участником нашей программы лояльности.</p>
		<p>С каждого заказа <span>Вам начисляется 10% бонусными рублями</span>, которые можно потратить до 100% на следующие покупки. И о как приятно видеть, когда мы доставляем подарки за 0 рублей! Так как в этот момент, мы понимаем, что Вы заказываете не первый раз и вам нравится наш сервис! А мы для этого стараемся ежедневно огромной командой!</p>
	</div>
</section>
<div class="thank">
	<p>Благодарим вас за выбор очень ягодной лаборатории VeryBerryLab</p>
</div><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>