import React from 'react';
import PropTypes from "prop-types";
import { FormattedMessage } from 'react-intl';
import { Radio } from '@deskpro/react-components';

class ContentTemplatesFilter extends React.Component {
  static propTypes = {
    templates:         PropTypes.object,
    filteredTemplates: PropTypes.object,
    handleShowMode:    PropTypes.func,
    handleFilter:      PropTypes.func,
    showMode:          PropTypes.string,
    filter:            PropTypes.string,
  };

  static defaultProps = {
    showMode: 'all',
    filter: '',
  };

  constructor(props) {
    super(props);
    this.state = {
      showOptions: [],
    }
  }

  componentWillMount() {
    if (this.props.filteredTemplates) {
      this.countShowOptions(this.props.filteredTemplates);
    }
  }

  componentWillReceiveProps(nextProps) {
    if (nextProps.filteredTemplates !== this.props.filteredTemplates) {
      this.countShowOptions(nextProps.filteredTemplates);
    }
  }

  countShowOptions = (templates) => {
    this.setState({
      showOptions: [
        {
          value: 'all',
          label: <FormattedMessage id="agent.content_templates.filter.all"/>,
          count: templates.count(),
        },
        {
          value: 'news',
          label: <FormattedMessage id="agent.content_templates.filter.news" />,
          count: templates.count(template => template.get('type') === 'news'),
        },
        {
          value: 'article',
          label: <FormattedMessage id="agent.content_templates.filter.article" />,
          count: templates.count(template => template.get('type') === 'article'),
        }
      ],
    });
  };

  render() {
    return (
      <div className="template_filtering">
        <div className="show block">
          <div className="title">
            <FormattedMessage id="agent.content_templates.filter.title"/>
          </div>
          <div className="template_filters">
            {this.state.showOptions.map(option =>
              <Radio
                key={option.value}
                className="template_filter_item"
                onChange={this.props.handleShowMode}
                checked={option.value === this.props.showMode}
                value={option.value}
              >
                <span className="label">{option.label}</span> ({option.count})
              </Radio>,
            )}
          </div>
        </div>
        <hr/>
      </div>
    );
  }
}

export default ContentTemplatesFilter;
