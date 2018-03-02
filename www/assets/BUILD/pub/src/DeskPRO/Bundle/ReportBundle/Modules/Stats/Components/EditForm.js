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
    vars:        PropTypes.array
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

    return (<reduxForm.Select key={name} name={name} options={choices} />);
  }

  static renderTypeField(name, values) {
    if (!values) {
      return null;
    }

    const choices = Object.keys(values).map((value) => {
      const choice = { label: value, value };
      return choice;
    });

    return (<reduxForm.Select label="Record Type" key={name} name={name} options={choices} />);
  }

  static renderTypeValueField(name, values) {
    if (!values) {
      return null;
    }

    const choices = Object.entries(values).map(([key, value]) => {
      const choice = { label: value[0], value: key };
      return choice;
    });

    return (<reduxForm.Select label="Default Value" key={name} name={name} options={choices} />);
  }

  onAddButtonClick = (event) => {
    event.preventDefault();
    event.stopPropagation();
    this.props.fields.push();
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
              <div className="remove-ctrl" onClick={() => fields.remove(index)}><i className="fa fa-trash" /></div>
              <reduxForm.Input
                label={hint}
                name={`${varName}.name`}
                validate={[VarsFieldComponent.validateVarName]}
              />
              <reduxForm.Select
                label="Type"
                options={varTypes}
                name={`${varName}.type`}
              />
              { this.isDateType(variable) &&
                VarsFieldComponent.renderDateField(`${varName}.field_value`, groupParams.dates) }
              { this.isVarType(variable) &&
                VarsFieldComponent.renderTypeField(`${varName}.field_type`, groupParams[variable.type]) }
              { this.varTypeHasValue(variable) &&
                VarsFieldComponent.renderTypeValueField(
                  `${varName}.field_value`,
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
    fields:  PropTypes.object.isRequired,
    options: PropTypes.array.isRequired,
  };

  render() {
    const { fields, options } = this.props;

    const newOptions = options.map(label => label.label);

    return (<reduxForm.TagSet
      name="labels"
      label="Labels"
      tags={fields.getAll() || []}
      options={newOptions}
    />);
  }
}

const VarsField = formValues('vars')(VarsFieldComponent);
const LabelsField = formValues('labels')(LabelsFieldComponent);

export class EditFormComponent extends React.Component {

  static defaultProps = {
    select:        '',
    groupBy:       '',
    dpqlParser:    null,
    change:        null,
    queryValues:   {},
    handleSubmit:  null,
    error:         null,
    labels:        [],
    extendedQuery: false
  };

  static propTypes = {
    groupParams:   PropTypes.object.isRequired,
    labels:        PropTypes.array.isRequired,
    select:        PropTypes.string,
    groupBy:       PropTypes.string,
    dpqlParser:    PropTypes.func,
    change:        PropTypes.func,
    queryValues:   PropTypes.object,
    handleSubmit:  PropTypes.func,
    error:         PropTypes.string,
    extendedQuery: PropTypes.bool,
  };

  static toDpql(fields) {
    const parts = [];
    parts.push(`SELECT ${fields.select || 'COUNT()'}`);
    parts.push(`FROM ${fields.from || '???'}`);
    if (fields.where) {
      parts.push(`WHERE ${fields.where}`);
    }
    if (fields.split_by) {
      parts.push(`SPLIT BY ${fields.split_by}`);
    }
    if (fields.order_by) {
      parts.push(`ORDER BY ${fields.order_by}`);
    }
    if (fields.group_by) {
      parts.push(`GROUP BY ${fields.group_by}`);
      if (fields.with_rollup) {
        parts.push('WITH ROLLUP');
      }
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

  constructor(props) {
    super(props);
    this.state = {
      queryInputMode:    props.extendedQuery ? 'dpql' : 'form',
      queryModeChanging: true
    };
  }

  componentDidMount() {
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
    const extendedQuery = this.props.queryValues.raw ? this.props.queryValues.raw.indexOf('LAYER WITH') !== -1 : false;
    if (extendedQuery) {
      console.info('Cant change mode to form, you\'re using extended query syntax');
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
        this.setState({ queryInputMode: to, queryModeChanging: false });
      }
    }
  };

  render() {
    const groupBy = this.props.groupBy || '';
    const { select, groupParams, labels } = this.props;

    const renderVars = field => <VarsField fields={field.fields} groupParams={groupParams || {}} />;
    const renderLabels = field => <LabelsField fields={field.fields} options={labels} />;

    return (
      <form onSubmit={this.props.handleSubmit}>
        <Container>
          <reduxForm.Input
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
                  <reduxForm.Input label="SELECT" name="select" />
                  <reduxForm.Input label="FROM" name="from" />
                  <reduxForm.Input label="WHERE" name="where" />
                  <reduxForm.Input label="SPLIT BY" name="split_by" />
                  <reduxForm.Input label="GROUP BY" name="group_by" />
                  <div
                    className={classNames({
                      'field-hidden': !(select && select.match(/count\s*\(.*?\)/i) && groupBy.length)
                    })}
                  >
                    <reduxForm.Checkbox
                      label="WITH ROLLUP - Adds a Total column to grouped COUNT queries made against hierarchies"
                      name="with_rollup"
                    />
                  </div>
                  <div style={{ width: '150px' }}>
                    <reduxForm.Input label="LIMIT" name="limit" />
                    <reduxForm.Input label="OFFSET" name="offset" />
                  </div>
                </Section>
                <Section hidden={this.state.queryInputMode !== 'dpql'}>
                  <reduxForm.Textarea name="raw" />
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
        {this.props.error && <div className="form-error-message">{this.props.error}</div>}
      </form>
    );
  }
}

export const EditForm = formValues({
  queryValues: 'query',
  select:      'query.select',
  groupBy:     'query.groupBy'
})(EditFormComponent);
