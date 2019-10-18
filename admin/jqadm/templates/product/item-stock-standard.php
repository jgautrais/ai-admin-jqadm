<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2015-2018
 */


$enc = $this->encoder();
$stockTypes = $this->get( 'stockTypes', [] );

$keys = ['stock.id', 'stock.siteid', 'stock.type', 'stock.stocklevel', 'stock.dateback', 'stock.timeframe'];


?>
<div id="stock" class="item-stock content-block tab-pane fade" role="tabpanel" aria-labelledby="stock">

	<table class="stock-list table table-default"
		data-items="<?= $enc->attr( $this->get( 'stockData', [] ) ); ?>"
		data-keys="<?= $enc->attr( $keys ) ?>"
		data-siteid="<?= $this->site()->siteid() ?>"
		data-numtypes="<?= count( $stockTypes ) ?>" >

		<thead>
			<tr>
				<th class="stock-type">
					<?php if( count( $stockTypes ) > 1 ) : ?>
						<span class="help"><?= $enc->html( $this->translate( 'admin', 'Type' ) ); ?></span>
						<div class="form-text text-muted help-text">
							<?= $enc->html( $this->translate( 'admin', 'Warehouse or local store if your articles are available at several locations' ) ); ?>
						</div>
					<?php endif; ?>
				</th>
				<th class="stock-stocklevel">
					<span class="help"><?= $enc->html( $this->translate( 'admin', 'Stock level' ) ); ?></span>
					<div class="form-text text-muted help-text">
						<?= $enc->html( $this->translate( 'admin', 'Number of articles currently in stock, leave empty for an unlimited quantity' ) ); ?>
					</div>
				</th>
				<th class="stock-dateback">
					<span class="help"><?= $enc->html( $this->translate( 'admin', 'Back in stock' ) ); ?></span>
					<div class="form-text text-muted help-text">
						<?= $enc->html( $this->translate( 'admin', 'Shown if the article reached a stock level of zero' ) ); ?>
					</div>
				</th>
				<th class="stock-timeframe">
					<span class="help"><?= $enc->html( $this->translate( 'admin', 'Delivery within' ) ); ?></span>
					<div class="form-text text-muted help-text">
						<?= $enc->html( $this->translate( 'admin', 'Usual time frame for the delivery of the product' ) ); ?>
					</div>
				</th>
				<th class="actions">
					<div v-if="(items || []).length < numtypes" class="btn act-add fa" tabindex="<?= $this->get( 'tabindex' ); ?>"
						title="<?= $enc->attr( $this->translate( 'admin', 'Insert new entry (Ctrl+I)' ) ); ?>"
						v-on:click="addItem()">
					</div>
				</th>
			</tr>
		</thead>
		<tbody>

			<tr v-for="(item, idx) in items" v-bind:key="idx" class="stock-row">
				<td v-bind:class="'stock-type mandatory ' + (item['css'] || '')">
					<?php if( count( $stockTypes ) > 1 ) : ?>
						<select is="select-component" class="form-control custom-select item-type" required
							v-bind:items="JSON.parse('<?= $enc->attr( $this->map( $stockTypes, 'stock.type.code', 'stock.type.label' )->toArray() ) ?>')"
							v-bind:name="'<?= $enc->attr( $this->formparam( ['stock', 'idx', 'stock.type'] ) ); ?>'.replace( 'idx', idx )"
							v-bind:text="'<?= $enc->html( $this->translate( 'admin', 'Please select' ) ); ?>'"
							v-bind:tabindex="'<?= $this->get( 'tabindex' ); ?>'"
							v-bind:readonly="checkSite(idx)"
							v-bind:value="item['stock.type']" >
						</select>
					<?php else : ?>
						<input class="item-type" type="hidden"
							v-bind:name="'<?= $enc->attr( $this->formparam( ['stock', 'idx', 'stock.type'] ) ); ?>'.replace( 'idx', idx )"
							value="<?= $enc->attr( key( $stockTypes ) ); ?>" />
					<?php endif; ?>
				</td>
				<td class="stock-stocklevel optional">
					<input class="form-control item-stocklevel" type="number" step="1" min="0" tabindex="<?= $this->get( 'tabindex' ); ?>"
						v-bind:name="'<?= $enc->attr( $this->formparam( ['stock', 'idx', 'stock.stocklevel'] ) ); ?>'.replace( 'idx', idx )"
						v-bind:readonly="checkSite(idx)"
						v-model="item['stock.stocklevel']" />
				</td>
				<td class="stock-dateback optional">
					<input class="form-control item-dateback" type="datetime-local" tabindex="<?= $this->get( 'tabindex' ); ?>"
						v-bind:name="'<?= $enc->attr( $this->formparam( ['stock', 'idx', 'stock.dateback'] ) ); ?>'.replace( 'idx', idx )"
						placeholder="<?= $enc->attr( $this->translate( 'admin', 'YYYY-MM-DD hh:mm:ss (optional)' ) ); ?>"
						v-bind:readonly="checkSite(idx)"
						v-model="item['stock.dateback']" />
				</td>
				<td class="stock-timeframe optional">
					<input class="form-control item-timeframe" type="text" tabindex="<?= $this->get( 'tabindex' ); ?>"
						v-bind:name="'<?= $enc->attr( $this->formparam( ['stock', 'idx', 'stock.timeframe'] ) ); ?>'.replace( 'idx', idx )"
						placeholder="<?= $enc->attr( $this->translate( 'admin', 'Time frame (optional)' ) ); ?>"
						v-bind:readonly="checkSite(idx)"
						v-model="item['stock.timeframe']" />
				</td>
				<td class="actions">
					<input class="item-id" type="hidden" v-model="item['stock.id']"
					v-bind:name="'<?= $enc->attr( $this->formparam( ['stock', 'idx', 'stock.id'] ) ); ?>'.replace( 'idx', idx )" />

					<div v-if="!checkSite(idx)" class="btn act-delete fa" tabindex="<?= $this->get( 'tabindex' ); ?>"
						title="<?= $enc->attr( $this->translate( 'admin', 'Delete this entry' ) ); ?>"
						v-on:click.stop="removeItem(idx)">
					</div>
				</td>
			</tr>

		</tbody>
	</table>

	<?= $this->get( 'stockBody' ); ?>

</div>
