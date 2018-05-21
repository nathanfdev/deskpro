import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import { Field } from '@deskpro/react-forms';
import classNames from 'classnames';
import { collectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { Input } from 'DeskPRO/Component/Semantic/ReactForm';
import { AbstractCustomField } from './AbstractCustomField';

@connect(state => ({
  currencies: collectionSelectorFactory('Currency', 'all')(state)
}))
class CustomFieldCurrencyContainer extends React.Component {

  render() {
    return <CustomFieldCurrency {...this.props} />;
  }
}

class CustomFieldCurrency extends AbstractCustomField {

  static propTypes = {
    currencies: PropTypes.object
  };

  render() {
    const { name, config, currencies } = this.props;
    const currencyId = config.getIn(['options', 'currency_id']);
    const currency = currencies.get(currencyId);

    return (
      <Field select={name}>
        <CurrencyField key={config.get('id')} currency={currency} />
      </Field>
    );
  }
}

class CurrencyField extends React.Component {

  static propTypes = {
    value:    PropTypes.number,
    onChange: PropTypes.func,
    currency: PropTypes.object
  };

  render() {
    const { value, onChange, currency } = this.props;

    return (
      <div className={classNames('currency-field', currency.get('currency_code').toLowerCase())}>
        <span className="currency-symbol">{currency.get('symbol')}</span>
        <Input type="text" value={value} onChange={onChange} />
      </div>
    );
  }
}

export default CustomFieldCurrencyContainer;
