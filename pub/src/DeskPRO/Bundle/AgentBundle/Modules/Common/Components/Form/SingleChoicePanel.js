import React, { Component, PropTypes } from 'react';
import {QuickFilter} from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Form/QuickFilter';
import classNames from 'classnames';
import { RadioChoiceMenuOption } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Form/ChoiceMenu';

export class SingleChoicePanel extends Component {
  static propTypes = {
    setParams: PropTypes.func.isRequired,
    resetSingleAction: PropTypes.func.isRequired,
    currentParams: PropTypes.object,
    item: PropTypes.object.isRequired,
    depth: PropTypes.bool
  };

  render() {
    const { item, setParams, currentParams, depth, resetSingleAction } = this.props;
    const renderNested = (nested, field) => {
      if (!nested || !nested.length) {
        return <span />;
      }

      return (
        <ul>
          {nested.map((option, index) =>
              <RadioChoiceMenuOption key={index}
                                     isActive={currentParams && currentParams.get(option.param) === option.value}
                                     resetSingleAction={resetSingleAction}
                                     value={option.value}
                                     param={option.param}
                                     label={option.label}
                                     field={field}
                                     setParams={setParams.bind(this)}/>
          )}
        </ul>
      );
    };

    const classes = classNames('dpw-navigation-dropdown-panel', { 'dpw-navigation-dropdown-panel-corner-left': depth });

    return (
      <div className={classes} style={{width: '250px'}}>
        <div className="dpw-navigation-dropdown-panel-content">
          <div className="dpw-navigation-dropdown-panel-content-line">
            <div className="dpw-navigation-dropdown-panel-content-full">
              <QuickFilter/>
            </div>
          </div>
          <div className="dpw-navigation-dropdown-panel-content-line">
            <div className="dpw-navigation-dropdown-panel-content-full">
              <div className="dpw--popup-item-collection">
                <ul>
                  {item.options.map((option, index) =>
                      <RadioChoiceMenuOption key={index}
                                             isActive={currentParams && currentParams.get(item.param) === option.value}
                                             resetSingleAction={resetSingleAction}
                                             value={option.value}
                                             label={option.label}
                                             field={item.field}
                                             param={item.param}
                                             setParams={setParams.bind(this)}>
                        {renderNested(option.nested, item.field)}
                      </RadioChoiceMenuOption>
                  )}
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    );
  }
}