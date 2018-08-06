import React from 'react';
import PropTypes from 'prop-types';
import { Button, Container, Tabs, TabLink, Section } from '@deskpro/react-components';
import { reduxForm } from '@deskpro/react-components/dist/bindings';
import { formValues, Field, FieldArray, FormSection } from 'redux-form';
import classNames from 'classnames';
import { varTypes } from './helper';

class VarsFieldComponent extends React.PureComponent {

  static defaultProps = {
    vars: []
  };

  static propTypes = {
    groupParams: PropTypes.object.isRequired,
    fields:      PropTypes.object.isRequired,
    vars:        PropTypes.array,
    change:      PropTypes.func
  };

  static validateVarName(name) {
    if (!name || name.length === 0) {
      return undefined;
    }
    if (!name.match(/^[A-Za-z0-9_]+$/)) {
      return 'Only letters, numbers and underscores are allowed.';
    }
    return undefined;
  }

  static renderDateField(name, dates) {
    if (!dates) {
      return null;
    }

    const choices = Object.entries(dates).map(([index, date]) => {
      const choice = { value: index, label: date[0] };
      return choice;
    });

    return (<reduxForm.Select label="Default Value" onChange={() => {}} key={name} name={name} options={choices} />);
  }

  static renderTypeField(name, values) {
    if (!values) {
      return null;
    }

    const choices = Object.keys(values).map((value) => {
      const choice = { label: value, value };
      return choice;
    });

    return (<reduxForm.Select onChange={() => {}} label="Record Type" key={name} name={name} options={choices} />);
  }

  static renderTypeValueField(name, values) {
    if (!values) {
      return null;
    }

    const choices = Object.entries(values).map(([key, value]) => {
      const choice = { label: value[0], value: key };
      return choice;
    });

    return (<reduxForm.Select onChange={() => {}} label="Default Value" key={name} name={name} options={choices} />);
  }

  constructor(props) {
    super(props);
    const { fields, vars } = props;
    const usesCustomTable = {};

    fields.forEach((varName, index) => {
      const variable = vars && vars[index] ? vars[index] : {};
      usesCustomTable[variable.name] = Boolean(variable.table);
      return variable;
    });

    this.state = {
      usesCustomTable
    };
  }

  onAddButtonClick = (event) => {
    event.preventDefault();
    event.stopPropagation();
    this.props.fields.push();
  };

  onCheckboxClick = (varName, varFormName) => {
    const { usesCustomTable } = this.state;
    usesCustomTable[varName] = !usesCustomTable[varName];
    if (!usesCustomTable[varName]) {
      this.props.change(`${varFormName}.table`, '');
    }
    this.setState(usesCustomTable);
  };

  // eslint-disable-next-line class-methods-use-this
  isDateType(variable) {
    return variable.type === 'dates';
  }

  isVarType(variable) {
    return variable.type && variable.type !== 'dates' && this.props.groupParams[variable.type];
  }

  varTypeHasValue(variable) {
    return this.isVarType(variable) && variable.field_type && this.props.groupParams[variable.type][variable.field_type];
  }

  renderCheckbox(varName, varFormName) {
    const inputProps = {
      onChange: () => {},
      onClick:  () => { this.onCheckboxClick(varName, varFormName); },
      type:     'checkbox'
    };
    if (this.state.usesCustomTable[varName]) {
      inputProps.checked = 'checked';
    }
    return (
      <label>
        <input {...inputProps} />
        Use special table
      </label>
    );
  }

  render() {
    const { fields, groupParams, vars } = this.props;

    return (
      <div className="varsfield-wrap">
        <div className="varsfield-list">
          {fields.map((varName, index) => {
            const key = index;
            const variable = vars && vars[index] ? vars[index] : {};

            let hint;

            if (!variable.name) {
              hint = 'ID';
            } else {
              hint =  (<span>ID as <em>{`\${${variable.name}}`}</em></span>);
            }

            return (<div className="varsfield-item" key={key}>
              <div className="remove-ctrl" onClick={() => fields.remove(index)}><i className="far fa-trash-alt" /></div>
              <reduxForm.Input
                onChange={() => {}}
                label={hint}
                name={`${varName}.name`}
                validate={[VarsFieldComponent.validateVarName]}
              />
              <reduxForm.Select
                onChange={() => {}}
                label="Type"
                options={varTypes}
                name={`${varName}.type`}
              />
              { this.isDateType(variable) &&
                VarsFieldComponent.renderDateField(`${varName}.default`, groupParams.dates) }
              { this.isVarType(variable) &&
                VarsFieldComponent.renderTypeField(`${varName}.field_type`, groupParams[variable.type]) }
              { this.isVarType(variable) && this.renderCheckbox(variable.name, varName)}
              { this.state.usesCustomTable[variable.name] ? <reduxForm.Input
                onChange={() => {}}
                name={`${varName}.table`}
              /> : null }
              { this.varTypeHasValue(variable) &&
                VarsFieldComponent.renderTypeValueField(
                  `${varName}.default`,
                  groupParams[variable.type][variable.field_type]
                ) }
            </div>);
          })}
        </div>
        <Button onClick={this.onAddButtonClick} type="secondary" size="medium">Add Variable</Button>
      </div>
    );
  }
}

class LabelsFieldComponent extends React.PureComponent {
  static defaultProps = {
    labels: []
  };

  static propTypes = {
    fields:   PropTypes.object.isRequired,
    options:  PropTypes.array.isRequired,
    isCustom: PropTypes.bool,
  };

  render() {
    const { fields, options, isCustom } = this.props;
    const newOptions = options.map(label => label.label);

    return (<reduxForm.TagSet
      onChange={() => {}}
      name="labels"
      label="Labels"
      tags={fields.getAll() || []}
      options={newOptions}
      editable={isCustom}
    />);
  }
}

const VarsField = formValues('vars')(VarsFieldComponent);
const LabelsField = formValues('labels')(LabelsFieldComponent);

export class EditFormComponent extends React.Component {

  static defaultProps = {
    select:       '',
    groupBy:      '',
    dpqlParser:   null,
    change:       null,
    queryValues:  {},
    handleSubmit: null,
    formErrors:   {},
    hasError:     false,
    labels:       [],
    isCustom:     false,
  };

  static propTypes = {
    groupParams:   PropTypes.object.isRequired,
    labels:        PropTypes.array.isRequired,
    select:        PropTypes.string,
    groupBy:       PropTypes.string,
    dpqlParser:    PropTypes.func,
    change:        PropTypes.func,
    initialize:    PropTypes.func,
    queryValues:   PropTypes.object,
    initialValues: PropTypes.object,
    handleSubmit:  PropTypes.func,
    formErrors:    PropTypes.object,
    hasError:      PropTypes.bool,
    isCustom:      PropTypes.bool,
  };

  static toDpql(fields) {
    const parts = [];
    parts.push(`SELECT ${fields.select || 'DPQL_COUNT()'}`);
    parts.push(`FROM ${fields.from || '???'}`);
    if (fields.where) {
      parts.push(`WHERE ${fields.where}`);
    }
    if (fields.split_by) {
      parts.push(`SPLIT BY ${fields.split_by}`);
    }
    if (fields.group_by) {
      parts.push(`GROUP BY ${fields.group_by}`);
      if (fields.with_rollup) {
        parts.push('WITH ROLLUP');
      }
    }
    if (fields.order_by) {
      parts.push(`ORDER BY ${fields.order_by}`);
    }
    if (fields.limit && fields.offset) {
      parts.push(`LIMIT ${fields.limit} OFFSET ${fields.offset}`);
    } else if (fields.limit) {
      parts.push(`LIMIT ${fields.limit}`);
    } else if (fields.offset) {
      parts.push(`LIMIT 1000, ${fields.offset}`);
    }
    return parts.join('\n');
  }

  static isExtendedQuery(props) {
    return props.queryValues.raw ? props.queryValues.raw.indexOf('LAYER WITH') !== -1 : false;
  }

  constructor(props) {
    super(props);
    this.state = {
      formErrors:        {},
      queryInputMode:    EditFormComponent.isExtendedQuery(props) ? 'dpql' : 'form',
      queryModeChanging: true
    };
  }

  componentDidMount() {
    this.props.initialize(this.props.initialValues);
    this.props.change('query_input_mode', this.state.queryInputMode);
  }

  shouldComponentUpdate(nextProps, nextState) {
    for (const k of Object.keys(nextProps)) {
      if (k !== 'queryValues' && nextProps[k] !== this.props[k]) {
        return true;
      }
    }
    for (const k of Object.keys(nextState)) {
      if (nextState[k] !== this.state[k]) {
        return true;
      }
    }
    return false;
  }

  queryModeChange = (to) => {
    const extendedQuery = EditFormComponent.isExtendedQuery(this.props);
    if (extendedQuery) {
      this.setState({ formErrors: { Mode: 'Can\'t change mode to form, you\'re using extended query syntax' } });
      console.info('Can\'t change mode to form, you\'re using extended query syntax');
      return;
    }

    this.props.change('query_input_mode', to);

    if (to === 'dpql') {
      const dpql = EditFormComponent.toDpql(this.props.queryValues || {});
      this.setState({ queryInputMode: to, queryModeChanging: false, origDpql: dpql });
      this.props.change('query.raw', dpql);
    } else if (this.props.queryValues.raw === this.state.origDpql) {
      this.setState({ queryInputMode: to, queryModeChanging: false });
    } else {
      this.setState({ queryInputMode: to, queryModeChanging: true });
      if (this.props.dpqlParser) {
        this.setState({ queryInputMode: to, queryModeChanging: true });
        this.props.dpqlParser(this.props.queryValues.raw).then((fields) => {
          if (this.state.queryInputMode === 'form') {
            this.setState({ queryModeChanging: false });
            Object.keys(fields).forEach((f) => {
              this.props.change(`query.${f}`, fields[f]);
            });
          }
        });
      } else {
        this.setState({ queryInputMode: to, queryModeChanging: false, formErrors: {} });
      }
    }
  };

  render() {
    const groupBy = this.props.groupBy || '';
    const { select, groupParams, labels, formErrors, hasError, isCustom } = this.props;

    const errors = { ...formErrors, ...this.state.formErrors };

    const renderVars = field => <VarsField change={this.props.change} fields={field.fields} groupParams={groupParams || {}} />;
    const renderLabels = field => <LabelsField fields={field.fields} options={labels} isCustom={isCustom} />;

    return (
      <form onSubmit={this.props.handleSubmit}>
        <Container>
          {hasError && <div className="form-error-message">Please check form accurate, there is an error.</div>}
          {Object.keys(errors).length > 0
            ? <div className="form-error-message">
              {Object.keys(errors).map(key => (<span>{key}: {errors[key]}<br /></span>))}
            </div>
            : null
          }
          <reduxForm.Input
            onChange={() => {}}
            label="Title"
            id="title"
            name="title"
            validate={reduxForm.validators.required}
          />
          <FieldArray name="labels" component={renderLabels} />
          <Field component="input" type="hidden" name="query_input_mode" />
          <div className="query-builder-input">
            <Tabs active={this.state.queryInputMode} onChange={this.queryModeChange}>
              <TabLink name="form">Query Builder</TabLink>
              <TabLink name="dpql">Raw DPQL</TabLink>
            </Tabs>
            <div className="input-wrap">
              <FormSection name="query">
                <Section hidden={this.state.queryInputMode !== 'form'}>
                  <reduxForm.Textarea autosize onChange={() => {}} label="SELECT" name="select" />
                  <reduxForm.Textarea autosize onChange={() => {}} label="FROM" name="from" />
                  <reduxForm.Textarea autosize onChange={() => {}} label="WHERE" name="where" />
                  <reduxForm.Textarea autosize onChange={() => {}} label="ORDER BY" name="order_by" />
                  <reduxForm.Textarea autosize onChange={() => {}} label="SPLIT BY" name="split_by" />
                  <reduxForm.Textarea autosize onChange={() => {}} label="GROUP BY" name="group_by" />
                  <div
                    className={classNames({
                      'field-hidden': !(select && select.match(/dpql_count\(.*\)/i) && groupBy.length)
                    })}
                  >
                    <reduxForm.Checkbox
                      className="rollup"
                      label="WITH ROLLUP - Adds a Total column to grouped COUNT queries made against hierarchies"
                      name="with_rollup"
                    />
                  </div>
                  <div style={{ width: '150px' }}>
                    <reduxForm.Input onChange={() => {}} label="LIMIT" name="limit" />
                    <reduxForm.Input onChange={() => {}} label="OFFSET" name="offset" />
                  </div>
                </Section>
                <Section hidden={this.state.queryInputMode !== 'dpql'}>
                  <reduxForm.Textarea name="raw" autosize />
                </Section>
              </FormSection>
              <div className="vars-wrap">
                <Section>
                  <FieldArray name="vars" component={renderVars} />
                </Section>
              </div>
            </div>
          </div>
        </Container>

      </form>
    );
  }
}

export const EditForm = formValues({
  queryValues: 'query',
  select:      'query.select',
  groupBy:     'query.group_by'
})(EditFormComponent);
