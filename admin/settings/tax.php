<?php if(!empty($data['success_message'])): ?>

<script type="text/javascript">
  (function($){
    $(document).ready(function(){
      $("#Cart66SuccessBox").fadeIn(1500).delay(4000).fadeOut(1500);
    })
  })(jQuery);
</script> 
  
<div class="Cart66Modal alert-message success" id="Cart66SuccessBox" style="">
  <p><strong><?php _e( 'Success' , 'cart66' ); ?></strong><br/>
  <?php echo $data['success_message'] ?></p>
</div>

<?php endif; ?>
<?php if(!empty($data['error_message'])): ?>

<script type="text/javascript">
  (function($){
    $(document).ready(function(){
      $("#Cart66ErrorBox").fadeIn(1500).delay(4000).fadeOut(1500);
    })
  })(jQuery);
</script> 
  
<div class="Cart66Modal alert-message alert-danger" id="Cart66ErrorBox" style="">
  <p><strong><?php _e( 'Error' , 'cart66' ); ?></strong><br/>
  <?php echo $data['error_message'] ?></p>
</div>

<?php endif; ?>
<form id="taxRatesForm" action="" method="post">
  <input type="hidden" name="cart66-action" value="save rate" />
  <h3><?php _e('Tax Rates', 'cart66'); ?></h3>
  <p class="description"><?php _e( 'If you would like to collect sales tax please enter the tax rate information below. You may enter tax rates for zip codes or states. If you are entering zip codes, you can enter individual zip codes or zip code ranges. A zip code range is entered with the low value separated from the high value by a dash. For example, 23000-25000. Zip code tax rates take precedence over state tax rates. You may also choose whether or not you want to apply taxes to shipping charges.' , 'cart66' ); ?></p>
  <p class="description"><?php _e( 'NOTE: If you are using PayPal Website Payments Standard you must set up the tax rate information', 'cart66'); ?> <strong><?php _e('in your PayPal account', 'cart66'); ?></strong></p>
  <table>
    <tbody>
      <tr valign="top">
        <td>
          <label for="tax-state"><?php _e( 'State' , 'cart66' ); ?>:</label>
            <select name="tax[state]" id="tax-state">
              <option value="">&nbsp;</option>
              <option value="All Sales"><?php _e( 'All Sales' , 'cart66' ); ?></option>
              <optgroup label="United States">
                <option value="AL">Alabama</option>
                <option value="AK">Alaska</option>
                <option value="AZ">Arizona</option>
                <option value="AR">Arkansas</option>
                <option value="CA">California</option>
                <option value="CO">Colorado</option>
                <option value="CT">Connecticut</option>
                <option value="DC">D. C.</option>
                <option value="DE">Delaware</option>
                <option value="FL">Florida</option>
                <option value="GA">Georgia</option>
                <option value="HI">Hawaii</option>
                <option value="ID">Idaho</option>
                <option value="IL">Illinois</option>
                <option value="IN">Indiana</option>
                <option value="IA">Iowa</option>
                <option value="KS">Kansas</option>
                <option value="KY">Kentucky</option>
                <option value="LA">Louisiana</option>
                <option value="ME">Maine</option>
                <option value="MD">Maryland</option>
                <option value="MA">Massachusetts</option>
                <option value="MI">Michigan</option>
                <option value="MN">Minnesota</option>
                <option value="MS">Mississippi</option>
                <option value="MO">Missouri</option>
                <option value="MT">Montana</option>
                <option value="NE">Nebraska</option>
                <option value="NV">Nevada</option>
                <option value="NH">New Hampshire</option>
                <option value="NJ">New Jersey</option>
                <option value="NM">New Mexico</option>
                <option value="NY">New York</option>
                <option value="NC">North Carolina</option>
                <option value="ND">North Dakota</option>
                <option value="OH">Ohio</option>
                <option value="OK">Oklahoma</option>
                <option value="OR">Oregon</option>
                <option value="PA">Pennsylvania</option>
                <option value="RI">Rhode Island</option>
                <option value="SC">South Carolina</option>
                <option value="SD">South Dakota</option>
                <option value="TN">Tennessee</option>
                <option value="TX">Texas</option>
                <option value="UT">Utah</option>
                <option value="VT">Vermont</option>
                <option value="VA">Virginia</option>
                <option value="WA">Washington</option>
                <option value="WV">West Virginia</option>
                <option value="WI">Wisconsin</option>
                <option value="WY">Wyoming</option>
              </optgroup>
              <optgroup label="Canada">
                <option value="AB">Alberta</option>
                <option value="BC">British Columbia</option>
                <option value="MB">Manitoba</option>
                <option value="NB">New Brunswick</option>
                <option value="NF">Newfoundland</option>
                <option value="NT">Northwest Territories</option>
                <option value="NS">Nova Scotia</option>
                <option value="NU">Nunavut</option>
                <option value="ON">Ontario</option>
                <option value="PE">Prince Edward Island</option>
                <option value="QC">Quebec</option>
                <option value="SK">Saskatchewan</option>
                <option value="YT">Yukon Territory</option>
              </optgroup>
            </select>
            
            <span class="description"> <?php _e( 'or' , 'cart66' ); ?> </span>
            <label for="tax-zip"> <?php _e( 'Zip' , 'cart66' ); ?>: </label>
            <input type="text" value="" id="tax-zip" name='tax[zip]' size="14" />
            <label for="tax-rate"> <?php _e( 'Rate' , 'cart66' ); ?>: </label>
            <input type="text" value="" id="tax-rate" name='tax[rate]'/> %
            <select name="tax[tax_shipping]">
              <option value="0"><?php _e( 'Don\'t tax shipping' , 'cart66' ); ?></option>
              <option value="1"><?php _e( 'Tax shipping' , 'cart66' ); ?></option>
            </select>
        </td>
      </tr>
    </tbody>
  </table>
  <br />
  <?php $rates = $data['rate']->getModels(); ?>
  <?php if(count($rates)): ?>
  <table class="widefat">
    <thead>
      <tr>
        <th><?php _e( 'Location' , 'cart66' ); ?></th>
        <th><?php _e( 'Rate' , 'cart66' ); ?></th>
        <th><?php _e( 'Tax Shipping' , 'cart66' ); ?></th>
        <th><?php _e( 'Actions' , 'cart66' ); ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($rates as $rate): ?>
       <tr>
         <td>
           <?php 
           if($rate->zip_low > 0) {
             if($rate->zip_low > 0) { echo str_pad($rate->zip_low, 5, "0", STR_PAD_LEFT); }
             if($rate->zip_high > $rate->zip_low) { echo '-' . str_pad($rate->zip_high, 5, "0", STR_PAD_LEFT); }
           }
           else {
             echo $rate->getFullStateName();
           }
           ?>
         </td>
         <td><?php echo Cart66Common::tax($rate->rate); ?></td>
         <td>
           <?php
           echo $rate->tax_shipping > 0 ? __("yes","cart66") : __("no","cart66");
           ?>
         </td>
         <td>
           <a class="delete" href="?page=cart66-settings&task=deleteTax&tab=tax_settings&id=<?php echo $rate->id ?>"><?php _e( 'Delete' , 'cart66' ); ?></a>
         </td>
       </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
  <table class="form-table">
    <tbody>
      <tr valign="top">
        <th scope="row">
          <?php submit_button(__('Save Rates', 'cart66')); ?>
        </th>
        <td></td>
      </tr>
    </tbody>
  </table>
</form>