const API_URL = (window.APP_CONFIG && window.APP_CONFIG.apiUrl) || 'http://localhost:8000/api';

const initialForms = {
  pnt: {
    titulo: '',
    guion: '',
    duracion_segundos: 0,
    formato: '',
    marca: '',
    notas: '',
  },
  tiempo: {
    nombre: '',
    duracion_total: 0,
  },
  carpeta: {
    nombre: '',
    fecha_emision: '',
    observaciones: '',
  },
  asignacionPnt: {
    tiempo_id: '',
    pnt_id: '',
  },
  asignacionTiempo: {
    carpeta_id: '',
    tiempo_id: '',
  },
  agenda: {
    date: '',
  },
};

function useForms() {
  const [forms, setForms] = React.useState(initialForms);

  const update = React.useCallback((key, field, value) => {
    setForms((prev) => ({
      ...prev,
      [key]: {
        ...prev[key],
        [field]: value,
      },
    }));
  }, []);

  const reset = React.useCallback((key) => {
    setForms((prev) => ({
      ...prev,
      [key]: initialForms[key],
    }));
  }, []);

  return { forms, update, reset };
}

function App() {
  const [loading, setLoading] = React.useState(true);
  const [error, setError] = React.useState(null);
  const [pnts, setPnts] = React.useState([]);
  const [tiempos, setTiempos] = React.useState([]);
  const [carpetas, setCarpetas] = React.useState([]);
  const [resumen, setResumen] = React.useState([]);
  const [agenda, setAgenda] = React.useState([]);
  const { forms, update, reset } = useForms();

  const fetchJson = React.useCallback(async (url, options = {}) => {
    const response = await fetch(url, {
      headers: {
        'Content-Type': 'application/json',
        ...(options.headers || {}),
      },
      ...options,
    });

    if (response.status === 204) {
      return null;
    }

    const body = await response.json().catch(() => null);

    if (!response.ok) {
      const message = (body && body.error) || 'Error inesperado';
      throw new Error(message);
    }

    return body;
  }, []);

  const refresh = React.useCallback(async () => {
    try {
      setLoading(true);
      const [pntsData, tiemposData, carpetasData, resumenData] = await Promise.all([
        fetchJson(`${API_URL}/pnts`),
        fetchJson(`${API_URL}/tiempos`),
        fetchJson(`${API_URL}/carpetas`),
        fetchJson(`${API_URL}/report/resumen`),
      ]);
      setPnts(pntsData);
      setTiempos(tiemposData);
      setCarpetas(carpetasData);
      setResumen(resumenData);
      setError(null);
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  }, [fetchJson]);

  React.useEffect(() => {
    refresh();
  }, [refresh]);

  const handleCreatePnt = async (event) => {
    event.preventDefault();
    try {
      await fetchJson(`${API_URL}/pnts`, {
        method: 'POST',
        body: JSON.stringify(forms.pnt),
      });
      reset('pnt');
      refresh();
    } catch (err) {
      setError(err.message);
    }
  };

  const handleDeletePnt = async (identifier) => {
    if (!window.confirm('¿Deseas eliminar el PNT seleccionado?')) return;
    try {
      await fetchJson(`${API_URL}/pnts/${identifier}`, {
        method: 'DELETE',
      });
      refresh();
    } catch (err) {
      setError(err.message);
    }
  };

  const handleCreateTiempo = async (event) => {
    event.preventDefault();
    try {
      await fetchJson(`${API_URL}/tiempos`, {
        method: 'POST',
        body: JSON.stringify(forms.tiempo),
      });
      reset('tiempo');
      refresh();
    } catch (err) {
      setError(err.message);
    }
  };

  const handleDeleteTiempo = async (identifier) => {
    if (!window.confirm('¿Eliminar tiempo y sus asociaciones?')) return;
    try {
      await fetchJson(`${API_URL}/tiempos/${identifier}`, {
        method: 'DELETE',
      });
      refresh();
    } catch (err) {
      setError(err.message);
    }
  };

  const handleCreateCarpeta = async (event) => {
    event.preventDefault();
    try {
      await fetchJson(`${API_URL}/carpetas`, {
        method: 'POST',
        body: JSON.stringify(forms.carpeta),
      });
      reset('carpeta');
      refresh();
    } catch (err) {
      setError(err.message);
    }
  };

  const handleDeleteCarpeta = async (identifier) => {
    if (!window.confirm('¿Eliminar carpeta programada?')) return;
    try {
      await fetchJson(`${API_URL}/carpetas/${identifier}`, {
        method: 'DELETE',
      });
      refresh();
    } catch (err) {
      setError(err.message);
    }
  };

  const handleAsignarPnt = async (event) => {
    event.preventDefault();
    if (!forms.asignacionPnt.tiempo_id || !forms.asignacionPnt.pnt_id) {
      setError('Selecciona un tiempo y un PNT para la asignación.');
      return;
    }
    try {
      await fetchJson(`${API_URL}/tiempos/${forms.asignacionPnt.tiempo_id}/pnts`, {
        method: 'POST',
        body: JSON.stringify({ pnt_id: forms.asignacionPnt.pnt_id }),
      });
      reset('asignacionPnt');
      refresh();
    } catch (err) {
      setError(err.message);
    }
  };

  const handleAsignarTiempo = async (event) => {
    event.preventDefault();
    if (!forms.asignacionTiempo.carpeta_id || !forms.asignacionTiempo.tiempo_id) {
      setError('Selecciona carpeta y tiempo para completar la asignación.');
      return;
    }
    try {
      await fetchJson(`${API_URL}/carpetas/${forms.asignacionTiempo.carpeta_id}/tiempos`, {
        method: 'POST',
        body: JSON.stringify({ tiempo_id: forms.asignacionTiempo.tiempo_id }),
      });
      reset('asignacionTiempo');
      refresh();
    } catch (err) {
      setError(err.message);
    }
  };

  const handleAgenda = async (event) => {
    event.preventDefault();
    try {
      const resultado = await fetchJson(`${API_URL}/report/agenda?date=${forms.agenda.date}`);
      setAgenda(resultado);
    } catch (err) {
      setError(err.message);
    }
  };

  const totalDuracionPnt = (tiempo) => {
    const pntDuracion = tiempo.pnt_ids
      .map((id) => pnts.find((item) => item.identifier === id))
      .filter(Boolean)
      .reduce((acc, item) => acc + (item.duracion_segundos || 0), 0);
    return (tiempo.duracion_total || 0) + pntDuracion;
  };

  return (
    <main>
      <h1>FutbolFolder CMS</h1>
      {error && <div className="error">{error}</div>}

      <section>
        <header>
          <h2>Nueva pieza PNT</h2>
          <span>Registra la publicidad disponible</span>
        </header>
        <form onSubmit={handleCreatePnt}>
          <label>
            Título
            <input
              value={forms.pnt.titulo}
              onChange={(event) => update('pnt', 'titulo', event.target.value)}
              required
            />
          </label>
          <label>
            Formato
            <input
              value={forms.pnt.formato}
              onChange={(event) => update('pnt', 'formato', event.target.value)}
              placeholder="Spot, mención, PNT"
            />
          </label>
          <label>
            Marca
            <input
              value={forms.pnt.marca}
              onChange={(event) => update('pnt', 'marca', event.target.value)}
            />
          </label>
          <label>
            Duración (segundos)
            <input
              type="number"
              min="0"
              value={forms.pnt.duracion_segundos}
              onChange={(event) => update('pnt', 'duracion_segundos', Number(event.target.value))}
            />
          </label>
          <label style={{ gridColumn: '1 / -1' }}>
            Guion
            <textarea
              value={forms.pnt.guion}
              onChange={(event) => update('pnt', 'guion', event.target.value)}
              required
            />
          </label>
          <label style={{ gridColumn: '1 / -1' }}>
            Notas
            <textarea
              value={forms.pnt.notas}
              onChange={(event) => update('pnt', 'notas', event.target.value)}
            />
          </label>
          <button type="submit">Guardar PNT</button>
        </form>

        {loading ? (
          <div className="empty-state">Cargando información…</div>
        ) : pnts.length === 0 ? (
          <div className="empty-state">Aún no se registraron PNTs.</div>
        ) : (
          <ul className="card-grid">
            {pnts.map((pnt) => (
              <li className="card" key={pnt.identifier}>
                <h3>{pnt.titulo}</h3>
                <span className="badge">{pnt.formato || 'Formato sin definir'}</span>
                <small className="meta">Marca: {pnt.marca || 'N/D'}</small>
                <p>{pnt.guion}</p>
                {pnt.notas && <p><strong>Notas:</strong> {pnt.notas}</p>}
                <small className="meta">Duración: {pnt.duracion_segundos} seg</small>
                <button className="secondary" onClick={() => handleDeletePnt(pnt.identifier)}>
                  Eliminar
                </button>
              </li>
            ))}
          </ul>
        )}
      </section>

      <section>
        <header>
          <h2>Planificación de tiempos</h2>
          <span>Organiza cortes y tandas</span>
        </header>
        <form onSubmit={handleCreateTiempo}>
          <label>
            Nombre del tiempo
            <input
              value={forms.tiempo.nombre}
              onChange={(event) => update('tiempo', 'nombre', event.target.value)}
              required
            />
          </label>
          <label>
            Duración base (segundos)
            <input
              type="number"
              min="0"
              value={forms.tiempo.duracion_total}
              onChange={(event) => update('tiempo', 'duracion_total', Number(event.target.value))}
            />
          </label>
          <button type="submit">Guardar tiempo</button>
        </form>

        <form onSubmit={handleAsignarPnt}>
          <label>
            Tiempo
            <select
              value={forms.asignacionPnt.tiempo_id}
              onChange={(event) => update('asignacionPnt', 'tiempo_id', event.target.value)}
              required
            >
              <option value="">Selecciona un tiempo</option>
              {tiempos.map((tiempo) => (
                <option key={tiempo.identifier} value={tiempo.identifier}>
                  {tiempo.nombre}
                </option>
              ))}
            </select>
          </label>
          <label>
            PNT
            <select
              value={forms.asignacionPnt.pnt_id}
              onChange={(event) => update('asignacionPnt', 'pnt_id', event.target.value)}
              required
            >
              <option value="">Selecciona un PNT</option>
              {pnts.map((pnt) => (
                <option key={pnt.identifier} value={pnt.identifier}>
                  {pnt.titulo}
                </option>
              ))}
            </select>
          </label>
          <button type="submit">Agregar PNT al tiempo</button>
        </form>

        {loading ? (
          <div className="empty-state">Cargando planificación…</div>
        ) : tiempos.length === 0 ? (
          <div className="empty-state">Aún no se registraron tiempos.</div>
        ) : (
          <ul className="card-grid">
            {tiempos.map((tiempo) => (
              <li className="card" key={tiempo.identifier}>
                <h3>{tiempo.nombre}</h3>
                <small className="meta">Duración total estimada: {totalDuracionPnt(tiempo)} seg</small>
                <div>
                  <strong>PNTs asignados:</strong>
                  {tiempo.pnt_ids.length === 0 ? (
                    <div className="empty-state" style={{ padding: '0.75rem', marginTop: '0.75rem' }}>
                      Sin asignaciones
                    </div>
                  ) : (
                    <ul className="list-inline">
                      {tiempo.pnt_ids.map((id) => {
                        const pnt = pnts.find((item) => item.identifier === id);
                        return <li key={id}>{pnt ? pnt.titulo : id}</li>;
                      })}
                    </ul>
                  )}
                </div>
                <button className="secondary" onClick={() => handleDeleteTiempo(tiempo.identifier)}>
                  Eliminar tiempo
                </button>
              </li>
            ))}
          </ul>
        )}
      </section>

      <section>
        <header>
          <h2>Carpetas programadas</h2>
          <span>Define qué sale al aire y cuándo</span>
        </header>
        <form onSubmit={handleCreateCarpeta}>
          <label>
            Nombre de carpeta
            <input
              value={forms.carpeta.nombre}
              onChange={(event) => update('carpeta', 'nombre', event.target.value)}
              required
            />
          </label>
          <label>
            Fecha de emisión
            <input
              type="date"
              value={forms.carpeta.fecha_emision}
              onChange={(event) => update('carpeta', 'fecha_emision', event.target.value)}
              required
            />
          </label>
          <label style={{ gridColumn: '1 / -1' }}>
            Observaciones
            <textarea
              value={forms.carpeta.observaciones}
              onChange={(event) => update('carpeta', 'observaciones', event.target.value)}
            />
          </label>
          <button type="submit">Guardar carpeta</button>
        </form>

        <form onSubmit={handleAsignarTiempo}>
          <label>
            Carpeta
            <select
              value={forms.asignacionTiempo.carpeta_id}
              onChange={(event) => update('asignacionTiempo', 'carpeta_id', event.target.value)}
              required
            >
              <option value="">Selecciona una carpeta</option>
              {carpetas.map((carpeta) => (
                <option key={carpeta.identifier} value={carpeta.identifier}>
                  {carpeta.nombre} ({carpeta.fecha_emision})
                </option>
              ))}
            </select>
          </label>
          <label>
            Tiempo
            <select
              value={forms.asignacionTiempo.tiempo_id}
              onChange={(event) => update('asignacionTiempo', 'tiempo_id', event.target.value)}
              required
            >
              <option value="">Selecciona un tiempo</option>
              {tiempos.map((tiempo) => (
                <option key={tiempo.identifier} value={tiempo.identifier}>
                  {tiempo.nombre}
                </option>
              ))}
            </select>
          </label>
          <button type="submit">Agregar tiempo a carpeta</button>
        </form>

        {loading ? (
          <div className="empty-state">Sincronizando…</div>
        ) : carpetas.length === 0 ? (
          <div className="empty-state">Aún no se registraron carpetas.</div>
        ) : (
          <ul className="card-grid">
            {carpetas.map((carpeta) => (
              <li className="card" key={carpeta.identifier}>
                <h3>{carpeta.nombre}</h3>
                <small className="meta">Emite el {carpeta.fecha_emision}</small>
                {carpeta.observaciones && <p>{carpeta.observaciones}</p>}
                <div>
                  <strong>Tiempos programados</strong>
                  {carpeta.tiempo_ids.length === 0 ? (
                    <div className="empty-state" style={{ padding: '0.75rem', marginTop: '0.75rem' }}>
                      Ningún tiempo asignado
                    </div>
                  ) : (
                    <ul className="list-inline">
                      {carpeta.tiempo_ids.map((id) => {
                        const tiempo = tiempos.find((item) => item.identifier === id);
                        return <li key={id}>{tiempo ? tiempo.nombre : id}</li>;
                      })}
                    </ul>
                  )}
                </div>
                <button className="secondary" onClick={() => handleDeleteCarpeta(carpeta.identifier)}>
                  Eliminar carpeta
                </button>
              </li>
            ))}
          </ul>
        )}
      </section>

      <section>
        <header>
          <h2>Agenda diaria</h2>
          <span>Consulta por fecha</span>
        </header>
        <form onSubmit={handleAgenda}>
          <label>
            Fecha
            <input
              type="date"
              value={forms.agenda.date}
              onChange={(event) => update('agenda', 'date', event.target.value)}
              required
            />
          </label>
          <button type="submit">Generar agenda</button>
        </form>
        {agenda.length === 0 ? (
          <div className="empty-state">Selecciona una fecha para ver su programación.</div>
        ) : (
          <ul className="card-grid">
            {agenda.map((item) => (
              <li className="card" key={item.carpeta.identifier}>
                <h3>{item.carpeta.nombre}</h3>
                <small className="meta">Emite el {item.carpeta.fecha_emision}</small>
                <div>
                  <strong>Tiempos</strong>
                  {item.tiempos.filter(Boolean).length === 0 ? (
                    <div className="empty-state" style={{ padding: '0.75rem', marginTop: '0.75rem' }}>
                      Sin tiempos planificados
                    </div>
                  ) : (
                    <ul className="list-inline">
                      {item.tiempos.filter(Boolean).map((tiempo) => (
                        <li key={tiempo.identifier}>{tiempo.nombre}</li>
                      ))}
                    </ul>
                  )}
                </div>
              </li>
            ))}
          </ul>
        )}
      </section>

      <section>
        <header>
          <h2>Resumen general</h2>
          <span>Totaliza duración y detalle</span>
        </header>
        {resumen.length === 0 ? (
          <div className="empty-state">Todavía no hay datos para el resumen.</div>
        ) : (
          <ul className="card-grid">
            {resumen.map((item) => (
              <li className="card" key={item.carpeta.identifier}>
                <h3>{item.carpeta.nombre}</h3>
                <small className="meta">
                  Fecha: {item.carpeta.fecha_emision} · Duración total calculada: {item.duracion_total_calculada} seg
                </small>
                {item.tiempos.map((detalle) => (
                  <div key={detalle.tiempo.identifier}>
                    <strong>{detalle.tiempo.nombre}</strong>
                    <small className="meta"> · {detalle.duracion_total_calculada} seg</small>
                    {detalle.pnts.length === 0 ? (
                      <div className="empty-state" style={{ padding: '0.75rem', marginTop: '0.75rem' }}>
                        Sin PNTs asignados
                      </div>
                    ) : (
                      <ul className="list-inline">
                        {detalle.pnts.map((pnt) => (
                          <li key={pnt.identifier}>
                            {pnt.titulo} ({pnt.duracion_segundos} seg)
                          </li>
                        ))}
                      </ul>
                    )}
                  </div>
                ))}
              </li>
            ))}
          </ul>
        )}
      </section>
    </main>
  );
}

ReactDOM.createRoot(document.getElementById('root')).render(<App />);
