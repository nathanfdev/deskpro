import PropTypes from 'prop-types';
import React from 'react';
import { Fieldset, createValue } from '@deskpro/react-forms';
import { Form, Field, PhoneInput } from 'DeskPRO/Component/Semantic/ReactForm';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import $ from 'jquery';
import Immutable from 'immutable';
import 'mark.js';
import debounce from 'lodash/debounce';
import classNames from 'classnames';
import NumberSelect from '../NumberSelect';
import DialGrid from '../../Common/DialGrid';

class Dialpad extends React.Component {

  static propTypes = {
    numbers:        PropTypes.object,
    outboundNumber: PropTypes.string,
    onMakeCall:     PropTypes.func,
    onSearchPerson: PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      formData: createValue({
        value: {
          call_from: props.numbers && props.numbers.size > 1 ? '' : props.numbers.first().get('id'),
          call_to:   props.outboundNumber
        },
        errorList: {},
        onChange:  this.onChange
      }),
      searchResults: Immutable.fromJS([]),
      submit:        false
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
  };

  onSubmit = (event) => {
    event.preventDefault();

    const { onMakeCall } = this.props;
    const { submit } = this.state;
    const { value } = this.state.formData;

    if (submit) {
      return;
    }

    const promise = onMakeCall(value.call_from, value.call_to);
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

  onSelectSearchResult = (number) => {
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

  onClearSearchResults = () => {
    setTimeout(() => {
      this.setState({
        searchResults: Immutable.fromJS([])
      });
    }, 1);
  };

  render() {
    const { numbers = Immutable.fromJS({}) } = this.props;
    const { formData, searchResults, submit } = this.state;

    return (
      <div className="dialpad">
        <Form formValue={formData} onSubmit={this.onSubmit}>
          <Fieldset>
            <Field select="call_from" label="Call from">
              <NumberSelect numbers={numbers} />
            </Field>
            <Field select="call_to">
              <PhoneInput supportSip ref={(c) => { this.phoneInput = c; }} />
            </Field>

            {searchResults.size > 0 &&
            <ClickOut onClickOut={this.onClearSearchResults}>
              <SearchResults
                query={formData.value.call_to}
                results={searchResults}
                onSelect={this.onSelectSearchResult}
              />
            </ClickOut>}

            <DialGrid onClick={this.onClickNumber} />

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
