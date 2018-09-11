import PropTypes from 'prop-types';
import React from 'react';
import { Fieldset, createValue } from '@deskpro/react-forms';
import { Form, Field, PhoneInput, Checkbox } from 'DeskPRO/Component/Semantic/ReactForm';
import { getPhoneCountryCode } from 'DeskPRO/Component/Util/PhoneNumber';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import $ from 'jquery';
import Immutable from 'immutable';
import 'mark.js/dist/jquery.mark';
import debounce from 'lodash/debounce';
import classNames from 'classnames';
import { storageAvailable } from 'DeskPRO/Component/Util/storageAvailable';
import NumberSelect from '../NumberSelect';
import DialGrid from '../../Common/DialGrid';

class Dialpad extends React.Component {

  static propTypes = {
    numbers:        PropTypes.object,
    lastCallFrom:   PropTypes.number,
    ticketId:       PropTypes.number,
    ticketTitle:    PropTypes.string,
    onMakeCall:     PropTypes.func,
    onSearchPerson: PropTypes.func
  };

  constructor(props) {
    super(props);
    let callFrom = '';
    if (props.numbers) {
      if (props.numbers.size > 1 && props.lastCallFrom && props.numbers.has(props.lastCallFrom)) {
        callFrom = props.numbers.get(props.lastCallFrom).get('id');
      } else if (props.numbers.size === 1) {
        callFrom = props.numbers.first().get('id');
      }
    }

    this.state = {
      formData: createValue({
        value: {
          call_from: callFrom,
          call_to:   '',
          ticket:    null
        },
        errorList: {},
        onChange:  this.onChange
      }),
      searchResults: Immutable.fromJS([]),
      submit:        false,
      ticketId:      props.ticketId,
      ticketTitle:   props.ticketTitle
    };
  }

  componentDidMount() {
    this.lastQuery = '';
  }

  componentWillReceiveProps(newProps) {
    const $input = $(this.phoneInput.input);
    const { formData } = this.state;

    if (newProps.outboundNumber) {
      this.setState({
        formData: createValue({
          value:     formData.value,
          errorList: {},
          onChange:  this.onChange
        }),
        searchResults: Immutable.fromJS([])
      }, () => {
        this.phoneInput.setNumber(`${newProps.outboundNumber}`);
        $input.focus();
      });
    }
  }

  onChange = (formData, changedFields) => {
    this.setState({ formData });

    const $input = $(this.phoneInput.input);
    const { onSearchPerson } = this.props;
    const searchPeople = debounce(() => {
      const callTo = this.state.formData.value.call_to;
      if (callTo && this.lastQuery !== callTo) {
        const promise = onSearchPerson(callTo);
        promise.success(({ data }) => {
          if (callTo === this.state.formData.value.call_to) {
            this.setState({
              searchResults: Immutable.fromJS(data)
            });
          }
        });
      } else {
        this.setState({
          searchResults: Immutable.fromJS([])
        });
      }

      this.lastQuery = callTo;
    }, 500);

    if (changedFields.indexOf('call_to') !== -1) {
      searchPeople();
    }

    // if just 'call to' field was changed then
    // try to set find appropriate 'call from' number
    if (changedFields.indexOf('call_to') !== -1 && changedFields.indexOf('call_from') === -1) {
      // update 'call from' field based on current country code
      const countryCode = this.phoneInput.getCountryData().iso2;
      const { numbers = Immutable.fromJS({}) } = this.props;

      let selectedNumber;

      // try to get from country code
      if (!selectedNumber) {
        numbers.forEach((number) => {
          if (number.get('outbound_calls_default')
            && number.get('outbound_calls_default_type') === 'country'
            && countryCode && countryCode.toUpperCase() === getPhoneCountryCode(number.get('number'))
          ) {
            selectedNumber = number;
          }
        });
      }

      // try to get from specific countries
      numbers.forEach((number) => {
        if (number.get('outbound_calls_default')
          && number.get('outbound_calls_default_type') === 'specific'
          && number.get('outbound_calls_default_countries').contains(countryCode)
        ) {
          selectedNumber = number;
        }
      });

      // try to get global outgoing number
      if (!selectedNumber) {
        numbers.forEach((number) => {
          if (number.get('outbound_calls_default')
            && number.get('outbound_calls_default_type') === 'all'
          ) {
            selectedNumber = number;
          }
        });
      }

      if (!selectedNumber) {
        // try to get last selected number
        if (storageAvailable('localStorage')) {
          const storedNumberId = localStorage.getItem('dpAgent.voice.lastCallFrom');
          if (storedNumberId) {
            selectedNumber = numbers.get(parseInt(storedNumberId, 10));
          }
        }
      }

      if (selectedNumber) {
        if (formData.value.call_from !== selectedNumber.get('id')) {
          setTimeout(() => {
            formData.value.call_from = selectedNumber.get('id');
            this.setState({
              formData: createValue({
                value:     formData.value,
                errorList: {},
                onChange:  this.onChange
              }),
              searchResults: Immutable.fromJS([])
            }, () => $input.focus());
          }, 1);
        }
      }
    } else if (changedFields.indexOf('call_from') !== -1) {
      // 'call from' number was manually changed, store user's choice in local storage
      if (storageAvailable('localStorage')) {
        localStorage.setItem('dpAgent.voice.lastCallFrom', formData.value.call_from);
      }
    }
  };

  onSubmit = (event) => {
    event.preventDefault();

    const { onMakeCall } = this.props;
    const { submit } = this.state;
    const { value } = this.state.formData;

    if (submit) {
      return;
    }

    const promise = onMakeCall(value.call_from, value.call_to, value.ticket);
    if (!promise) {
      return;
    }

    this.setState({
      submit:        true,
      searchResults: Immutable.fromJS([])
    });

    // handle just error callback, on success the dialpad component will be unmounted
    promise.error(({ errors }) => {
      const { formData } = this.state;
      this.setState({
        formData: createValue({
          value:     formData.value,
          errorList: errors,
          onChange:  this.onChange
        }),
        submit:        false,
        searchResults: Immutable.fromJS([])
      });
    });
  };

  onClickNumber = (number) => {
    const $input = $(this.phoneInput.input);
    const { formData } = this.state;
    const currentValue = formData.value.call_to || '';

    this.setState({
      formData: createValue({
        value:     formData.value,
        errorList: {},
        onChange:  this.onChange
      })
    }, () => {
      this.phoneInput.setNumber(`${currentValue}${number}`);
      $input.focus();
    });
  };

  onClearSearchResults = () => {
    setTimeout(() => {
      this.setState({
        searchResults: Immutable.fromJS([])
      });
    }, 1);
  };

  setOutgoingNumber = (number) => {
    const $input = $(this.phoneInput.input);
    const { formData } = this.state;

    setTimeout(() => {
      this.setState({
        formData: createValue({
          value:     formData.value,
          errorList: {},
          onChange:  this.onChange
        }),
        searchResults: Immutable.fromJS([])
      }, () => {
        this.phoneInput.setNumber(number);
        $input.focus();
      });
    }, 1);
  };

  setTicket = (ticketId, ticketTitle) => {
    const { formData } = this.state;
    formData.value.ticket = ticketId;

    this.setState({
      formData,
      ticketId,
      ticketTitle
    });
  };

  render() {
    const { numbers = Immutable.fromJS({}) } = this.props;
    const { formData, searchResults, submit, ticketId, ticketTitle } = this.state;

    return (
      <div className="dialpad">
        <Form formValue={formData} onSubmit={this.onSubmit}>
          <Fieldset>
            <Field select="call_to">
              <PhoneInput supportSip ref={(c) => { this.phoneInput = c; }} />
            </Field>

            {searchResults.size > 0 &&
            <ClickOut onClickOut={this.onClearSearchResults}>
              <SearchResults
                query={formData.value.call_to}
                results={searchResults}
                onSelect={this.setOutgoingNumber}
              />
            </ClickOut>}

            <DialGrid onClick={this.onClickNumber} />

            <Field select="call_from" label="Call from">
              <NumberSelect numbers={numbers} />
            </Field>
            {ticketId &&
            <Field select="ticket">
              <Checkbox
                choice={ticketId}
                label={`Attach this call to the open ticket (#${ticketId}): ${ticketTitle}`}
              />
            </Field>}

            <button className={classNames('ui button green call-button', { loading: submit })}>
              <i className="icon call" />
              Call
            </button>
          </Fieldset>
        </Form>
      </div>
    );
  }
}

class SearchResults extends React.Component {

  static propTypes = {
    query:    PropTypes.string,
    results:  PropTypes.object,
    onSelect: PropTypes.func
  };

  componentDidMount() {
    this.hightlightQuery();
  }

  componentDidUpdate() {
    this.hightlightQuery();
  }

  hightlightQuery() {
    const { query } = this.props;
    const $context = $('.dialpad-search-results');

    $context.unmark();

    if (query) {
      $context.mark(query);
    }
  }

  render() {
    const { results, onSelect } = this.props;
    const getNumber = item => item.getIn(['phone_numbers', 0, 'number']);

    return (
      <div className="dialpad-search-results">
        {results.map((item, index) =>
          <div
            key={index}
            className="dialpad-search-result-item"
            onClick={() => { onSelect(getNumber(item)); }}
          >
            {item.get('name')} {getNumber(item)}
          </div>
        )}
      </div>
    );
  }
}

export default Dialpad;
