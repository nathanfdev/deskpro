import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import { Modal, Select, Button } from '@deskpro/react-components';
import { FormattedMessage } from 'react-intl';
import $ from 'jquery';
import { allContentTemplatesSelector } from '../Selectors/contentTemplates';
import { loadContentTemplates } from '../Actions/contentTemplateActions';

@connect(state => ({
  contentTemplates: allContentTemplatesSelector(state)
}), null, null, { withRef: true })
class UseContentTemplatesModalContainer extends React.Component {

  static propTypes = {
    type:             PropTypes.string,
    contentTemplates: PropTypes.object,
    dispatch:         PropTypes.func,
    closeMenu:        PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      selectedTemplate: null
    };
  }

  componentDidMount() {
    this.props.dispatch(loadContentTemplates(true));
  }

  createFromTemplate = () => {
    const { type, closeMenu, contentTemplates } = this.props;
    const { selectedTemplate } = this.state;
    if (!selectedTemplate) {
      return;
    }

    const contentTemplate = contentTemplates.get(selectedTemplate);
    if (!contentTemplate) {
      return;
    }

    const setFormFields = (page) => {
      contentTemplate.get('template').forEach((field) => {
        const $el = $(page.form).find(`[name="${field.get('name')}"]`);
        if ($el.is('textarea')) {
          if ($el.closest('.fr-box')) {
            $el.froalaEditor('html.set', field.get('value'));
          } else {
            $el.html(field.get('value')).trigger('change');
          }
        } else if ($el.is(':checkbox, :radio')) {
          $el.each((i, v) => {
            $(v).attr('checked', field.get('value') === $(v).val());
          });
        } else {
          $el.val(field.get('value')).trigger('change');
        }
      });
    };

    if (type === 'article') {
      window.DeskPRO_Window.newArticleLoader.open(setFormFields);
    }

    closeMenu();
  };

  selectTemplate = ({ value }) => {
    this.setState({
      selectedTemplate: value
    });
  };

  render() {
    return (
      <UseContentTemplatesModal
        {...this.props}
        {...this.state}
        selectTemplate={this.selectTemplate}
        createFromTemplate={this.createFromTemplate}
      />
    );
  }
}

class UseContentTemplatesModal extends React.Component {

  static propTypes = {
    selectedTemplate:   PropTypes.object,
    contentTemplates:   PropTypes.object,
    closeMenu:          PropTypes.func,
    type:               PropTypes.string,
    selectTemplate:     PropTypes.func,
    createFromTemplate: PropTypes.func
  };

  getModalTitle = () => {
    const { type } = this.props;
    if (type === 'article') {
      return <FormattedMessage id="agent.general.create_article_from_template" />;
    }

    return '';
  };

  render() {
    const { closeMenu, selectTemplate, createFromTemplate, contentTemplates, selectedTemplate } = this.props;
    const options = contentTemplates.toArray().map(contentTemplate => ({
      value: contentTemplate.get('id'),
      label: contentTemplate.get('title')
    }));

    return (
      <div id="use_content_templates__modal">
        <Modal
          title={this.getModalTitle()}
          closeModal={closeMenu}
          buttons={
            <div>
              <Button
                type="secondary"
                size="large"
                className="right"
                onClick={createFromTemplate}
                disabled={!selectedTemplate}
              >
                <FormattedMessage id="agent.general.create" />
              </Button>
              <Button type="secondary" size="large" className="right" onClick={closeMenu}>
                <FormattedMessage id="agent.general.cancel" />
              </Button>
            </div>
          }
        >
          <Select
            value={selectedTemplate}
            options={options}
            clearable={false}
            searchable={false}
            onChange={selectTemplate}
          />
        </Modal>
      </div>
    );
  }
}

export default UseContentTemplatesModalContainer;
